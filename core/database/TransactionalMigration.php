<?php

declare(strict_types=1);

namespace app\core\database;

use Throwable;

trait TransactionalMigration
{

    /**
     * @throws Throwable
     */
    protected function runInTransaction(callable $fn): void
    {
        $this->db->beginTransaction();
        try {
            $fn();
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

}