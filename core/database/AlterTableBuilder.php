<?php

declare(strict_types=1);

namespace app\core\database;

use PDO;

final class AlterTableBuilder
{

    private PDO $db;
    private string $table;
    /** @var string[]  Cada elemento es una sentencia parcial (ADD COLUMN, DROP COLUMN, …) */
    private array $operations = [];

    public function __construct(PDO $db, string $table)
    {
        $this->db    = $db;
        $this->table = $table;
    }

    public function addColumn(string $name, string $type, array $options = []): self
    {
        $colDef = TableUtils::columnDefinition($name, $type, $options);
        $this->operations[] = "ADD COLUMN $colDef";
        return $this;
    }

    public function modifyColumn(string $name, string $type, array $options = []): self
    {
        $colDef = TableUtils::columnDefinition($name, $type, $options);
        $this->operations[] = "MODIFY COLUMN $colDef";
        return $this;
    }

    public function renameColumn(string $oldName, string $newName, string $type, array $options = []): self
    {
        $colDef = TableUtils::columnDefinition($newName, $type, $options);
        $this->operations[] = "CHANGE COLUMN `$oldName` $colDef";
        return $this;
    }

    public function dropColumn(string $name): self
    {
        $this->operations[] = "DROP COLUMN `$name`";
        return $this;
    }

    public function addPrimaryKey(string|array $columns): self
    {
        $cols = $this->quoteColumns((array) $columns);
        $this->operations[] = "ADD PRIMARY KEY ($cols)";
        return $this;
    }

    public function addUnique(string $name, string|array $columns): self
    {
        $cols = $this->quoteColumns((array) $columns);
        $this->operations[] = "ADD UNIQUE KEY `$name` ($cols)";
        return $this;
    }

    public function addIndex(string $name, string|array $columns): self
    {
        $cols = $this->quoteColumns((array) $columns);
        $this->operations[] = "ADD KEY `$name` ($cols)";
        return $this;
    }

    public function apply(): void
    {
        if (empty($this->operations)) {
            return;
        }

        $sql = "ALTER TABLE `$this->table`\n  " . implode(",\n  ", $this->operations) . ";";
        $this->db->exec($sql);
    }

    private function quoteColumns(array $cols): string
    {
        return implode(', ', array_map(fn($c) => "`$c`", $cols));
    }

}