<?php

declare(strict_types=1);

namespace app\migrations;

use app\core\database\migration\Migration;
use app\core\database\migration\Seeder;

class m2026_10_09_163255_procedure extends Migration implements Seeder
{

    public function up(): void
    {
        $this->db->exec("CREATE TABLE `procedure` (
            `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
            `procedure` char(5) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
        
        $this->db->exec("ALTER TABLE `procedure`
            ADD UNIQUE KEY `procedure` (`procedure`);");
    }

    public function down(): void
    {
        $this->db->exec("DROP TABLE `procedure`;");
    }

    public function seed(): void
    {
        $this->batchInsert('procedure', [
            ['procedure' => 'H2017'],
            ['procedure' => 'H2019'],
            ['procedure' => 'T1017'],
        ]);
    }
}