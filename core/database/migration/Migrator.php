<?php

declare(strict_types=1);

namespace app\core\database\migration;

use app\core\database\Database;
use PDO;
use ReflectionClass;
use ReflectionException;

final class Migrator
{

    private PDO $db;

    public function __construct(array $config, private readonly string $path)
    {
        $this->db = Database::load($config);
        $this->ensureMigrationsTable();
    }

    private function ensureMigrationsTable(): void
    {
        $this->db->exec("CREATE TABLE IF NOT EXISTS migrations (
            id INTEGER PRIMARY KEY AUTO_INCREMENT,
            migration VARCHAR(255) NOT NULL,
            batch INTEGER NOT NULL
        );");
    }

    public function getAppliedMigrations(): array
    {
        return $this->db->query("SELECT migration FROM migrations")
            ->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getMigrationFiles(): array
    {
        return array_diff(scandir($this->path), ['.', '..']);
    }

    public function up(): void
    {
        $applied = $this->getAppliedMigrations();
        $files = $this->getMigrationFiles();

        $toRun = array_diff($files, $applied);

        if (empty($toRun)) {
            echo "There are no pending migrations.\n";
            return;
        }

        $batch = time();

        foreach ($toRun as $file) {
            require $this->path . '/' . $file;

            $class = "app\\migrations\\" . pathinfo($file, PATHINFO_FILENAME);

            if (!class_exists($class)) {
                echo "Class $class not found.\n";
                continue;
            }

            $ref = new ReflectionClass($class);

            try {
                echo "Executing: $file\n";

                $migration = $ref->newInstance($this->db);
                $migration->up();

                if ($migration instanceof Seeder) {
                    if ($this->askConfirmation("Do you want to run the seed?")) {
                        echo "Running seed...\n";

                        $migration->seed();
                    } else {
                        echo "Seed cancelled.\n";
                    }
                }

                $stmt = $this->db->prepare("INSERT INTO migrations (migration, batch) VALUES (?,?)");
                $stmt->execute([$file, $batch]);
            } catch (ReflectionException $e) {
                echo "Class $class cannot be instantiated.\n";
            }
        }
    }

    public function down(): void
    {
        $lastBatch = $this->db->query("SELECT batch FROM migrations ORDER BY id DESC LIMIT 1")
            ->fetchColumn();

        if (!$lastBatch) {
            echo "There are no migrations to reverse.\n";
            return;
        }

        $stmt = $this->db->prepare("SELECT migration FROM migrations WHERE batch = ?");
        $stmt->execute([$lastBatch]);
        $migrations = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($migrations as $file) {
            require $this->path . '/' . $file;

            $class = "app\\migrations\\" . pathinfo($file, PATHINFO_FILENAME);

            if (!class_exists($class)) {
                echo "Class $class not found.\n";
                continue;
            }

            $ref = new ReflectionClass($class);

            try {
                echo "Reversing: $file\n";

                $migration = $ref->newInstance($this->db);
                $migration->down();

                $del = $this->db->prepare("DELETE FROM migrations WHERE migration = ?");
                $del->execute([$file]);
            } catch (ReflectionException $e) {
                echo "Class $class cannot be instantiated.\n";
            }
        }
    }

    private function askConfirmation(string $question, bool $default = true): bool
    {
        $options = $default ? '[Y/n]' : '[y/N]';

        while (true) {
            echo "$question $options: ";

            $response = strtolower(trim(fgets(STDIN)));

            if ($response === '') {
                return $default;
            }

            if (in_array($response, ['y', 'yes'], true)) {
                return true;
            }

            if (in_array($response, ['n', 'no'], true)) {
                return false;
            }

            echo "Please, type Y o N.\n";
        }
    }

}