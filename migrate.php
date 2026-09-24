#!/usr/bin/env php
<?php

declare(strict_types=1);

use app\core\database\Migrator;
use app\core\helpers\ArrayHelper;

include 'vendor/autoload.php';

$command = $argv[1] ?? null;

if (!in_array($command, ['up', 'down', 'make'])) {
    echo "Use: php migrate [up|down|make <name>]\n";
    exit(1);
}

const BASE_PATH = __DIR__ . DIRECTORY_SEPARATOR;
const MIGRATION_PATH = BASE_PATH . 'migrations' . DIRECTORY_SEPARATOR;

if (!is_dir(MIGRATION_PATH)) {
    mkdir(MIGRATION_PATH, 0755, true);
}

$config = ArrayHelper::merge(
    require_once BASE_PATH . 'config/web.php',
//    require_once BASE_PATH . 'config/web.local.php',
    require_once BASE_PATH . 'config/web.local-laptop.php',
);

$migrator = new Migrator($config['db'], MIGRATION_PATH);

switch ($command) {
    case 'up':
        $migrator->up();
        break;

    case 'down':
        $migrator->down();
        break;

    case 'make':
        $name = $argv[2] ?? 'migration';
        $file = 'm' . date('Y_m_d_His') . "_" . $name;

        $content = <<< PHP
        <?php
        
        declare(strict_types=1);

        namespace app\migrations;
        
        use app\core\database\Migration;
        
        class $file extends Migration
        {
        
            public function up(): void
            {
                // TODO insert code here
                //\$this->db->exec("CREATE TABLE `$name` (
                //    `id` char(36) NOT NULL
                //) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
                
                //\$this->db->exec("ALTER TABLE `$name`
                //    ADD PRIMARY KEY (`id`);");
            }
        
            public function down(): void
            {
                // TODO insert code here
                //\$this->db->exec("DROP TABLE `$name`;");
            }
            
        }
        PHP;

        file_put_contents(MIGRATION_PATH . $file . ".php", $content);

        echo "Migration created: $file\n";
        break;
}