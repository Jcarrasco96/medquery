<?php

declare(strict_types=1);

namespace app\models;

use app\core\database\query\DeleteSafeQuery;
use app\core\database\query\InsertSafeQuery;
use app\core\database\query\SelectSafeQuery;
use app\core\database\query\UpdateSafeQuery;
use app\core\Record;

final class Procedure extends Record
{

    public int $id;
    public string $procedure;

    public string $scenario = 'DEFAULT';

    public const string SCENARIO_CREATE = 'CREATE';
    public const string SCENARIO_UPDATE = 'UPDATE';

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? -1;

        if (isset($data['procedure'])) {
            $this->procedure = $data['procedure'];
        }
    }

    public static function findRange(int $page, int $limit, string $order): array
    {
        $data = new SelectSafeQuery()
            ->from('procedure')
            ->data();

        $count = $data->count();

        $data = $data->applyQueryParams([
            'page' => $page,
            'limit' => $limit,
            'order' => $order,
        ])->execute();

        $data = array_map(static fn(array $data) => self::fromArray($data), $data);

        return [$data, $count];
    }

    public function findByProcedure(string $procedure): ?Procedure
    {
        $data = new SelectSafeQuery()
            ->from(self::tableName())
            ->data()
            ->where('procedure', $procedure)
            ->execute();

        $data = array_shift($data);

        return empty($data) ? null : self::fromArray($data);
    }

    public static function findAll(): array
    {
        $data = new SelectSafeQuery()
            ->from(self::tableName())
            ->data()
            ->orderBy('procedure')
            ->execute();

        return array_map(static fn(array $data) => self::fromArray($data), $data);
    }

    protected static function tableName(): string
    {
        return "procedure";
    }

    public function create(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        return new InsertSafeQuery()
            ->from(self::tableName())
            ->data([
                'procedure' => $this->procedure,
            ])
            ->execute();
    }

    public function update(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        return new UpdateSafeQuery()
            ->from(self::tableName())
            ->data([
                'procedure' => $this->procedure,
            ])
            ->where('id', $this->id)
            ->execute();
    }

    public function delete(): bool
    {
        return new DeleteSafeQuery()
            ->from(self::tableName())
            ->where('id', $this->id)
            ->execute();
    }

    public function validate(bool $clearErrors = true): bool
    {
        if ($clearErrors) {
            $this->errors = [];
        }

        $record = self::findByProcedure($this->procedure);

        if ($this->scenario == self::SCENARIO_CREATE && $record) {
            $this->addError('procedure', 'Procedure already exists.');
            return false;
        }

        if ($this->scenario == self::SCENARIO_UPDATE && $record && $record->id != $this->id) {
            $this->addError('procedure', 'Procedure already exists.');
            return false;
        }

        if (empty($this->procedure)) {
            $this->addError('procedure', 'Field "procedure" is required.');
            return false;
        }

        if (strlen($this->procedure) != 5) {
            $this->addError('procedure', 'Procedure only allow 5 characters.');
            return false;
        }

        return empty($this->errors);
    }

    public function load(array $data = []): void
    {
        if (isset($data['procedure'])) {
            $this->procedure = $data['procedure'];
        }
    }
}