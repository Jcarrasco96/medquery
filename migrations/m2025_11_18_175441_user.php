<?php

declare(strict_types=1);

namespace app\migrations;

use app\core\database\Migration;
use app\core\services\Security;
use Exception;
use Ramsey\Uuid\Uuid;

class m2025_11_18_175441_user extends Migration
{

    public function up(): void
    {
        $this->db->exec("CREATE TABLE `user` (
            `id` char(36) NOT NULL,
            `name` varchar(100) NOT NULL,
            `email` varchar(255) NOT NULL,
            `password` char(60) NOT NULL,
            `auth_key` char(32) NOT NULL,
            `is_admin` tinyint(1) NOT NULL DEFAULT 0,
            `status` tinyint(1) NOT NULL DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

        $this->db->exec("ALTER TABLE `user`
            ADD PRIMARY KEY (`id`),
            ADD UNIQUE KEY `email` (`email`);");

        try {
            $authKey = Security::generateRandomString();
            $password = Security::generatePasswordHash($authKey . 'admin');

            $this->insert('user', [
                'id' => Uuid::uuid4()->toString(),
                'name' => 'ADMINISTRATOR',
                'email' => 'admin@jcarrasco96.com',
                'password' => $password,
                'auth_key' => $authKey,
                'is_admin' => 1,
                'status' => 1,
            ]);

            echo "User created! Use this email and password: admin@jcarrasco96.com and admin.\n";
        } catch (Exception $e) {
            echo "Error creating user: " . $e->getMessage() . "\n";
        }
    }

    public function down(): void
    {
        $this->db->exec("DROP TABLE `user`;");
    }

}