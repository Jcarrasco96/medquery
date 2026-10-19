<?php

declare(strict_types=1);

namespace app\migrations;

use app\core\database\migration\Migration;

class m2026_09_29_180411_user_add_medicaid_credentials extends Migration
{

    public function up(): void
    {
        $this->db->exec("ALTER TABLE `user`
            ADD COLUMN `portal_username` VARCHAR(255) NULL,
            ADD COLUMN `portal_password_encrypted` TEXT NULL;");
    }

    public function down(): void
    {
        $this->db->exec("ALTER TABLE `user`
            DROP COLUMN `portal_password_encrypted`,
            DROP COLUMN `portal_username`;");
    }
    
}