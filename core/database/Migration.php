<?php

declare(strict_types=1);

namespace app\core\database;

use PDO;

abstract class Migration
{

    public function __construct(public PDO $db)
    {
    }

    abstract public function up();

    abstract public function down();

    public function insert(string $table, array $data): void
    {
        $columns = implode(',', array_keys($data));
        $placeholders = implode(',', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));
    }

    public function createTable(string $name, callable $callback): void
    {
        $table = new Table($name);
        $callback($table);
        $sql = $this->buildCreateTableSQL($table);
        $this->db->exec($sql);
    }

    public function dropTable(string $name): void
    {
        $this->db->exec("DROP TABLE IF EXISTS `$name`;");
    }

    public function alterTable(string $name, callable $callback): void
    {
        $alter = new AlterTableBuilder($this->db, $name);
        $callback($alter);
        $alter->apply();
    }

    private function buildCreateTableSQL(Table $table): string
    {
        $cols = implode(",\n    ", $table->getColumns());

        $constraints = [];

        if (!empty($table->getPrimaryKeys())) {
            $pk = implode(', ', array_map(fn($c) => "`$c`", $table->getPrimaryKeys()));
            $constraints[] = "PRIMARY KEY ($pk)";
        }

        $constraints = array_merge($constraints, $table->getUniqueKeys());

        $all = $cols;
        if ($constraints) {
            $all .= ",\n    " . implode(",\n    ", $constraints);
        }

        return <<< SQL
        CREATE TABLE `$table->name` (
            $all
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        SQL;
    }

}