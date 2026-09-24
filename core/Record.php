<?php

declare(strict_types=1);

namespace app\core;

use app\core\database\query\SelectSafeQuery;

abstract class Record extends Model
{

    public static function findById(string $id): ?static
    {
        $data = new SelectSafeQuery()
            ->from(static::tableName())
            ->data()
            ->where('id', $id)
            ->limit(1)
            ->execute();

        if (empty($data)) {
            return null;
        }

        return static::fromArray(array_shift($data));
    }

    public static function findByUserId(string $id): ?static
    {
        $data = new SelectSafeQuery()
            ->from(static::tableName())
            ->data()
            ->where('user_id', $id)
            ->limit(1)
            ->execute();

        if (empty($data)) {
            return null;
        }

        return static::fromArray(array_shift($data));
    }

    public function loadRelation(string $relation_name): void
    {
        //return $this->attributes[$relation_name] ?? throw new NotFoundHttpException("The relation '$relation_name' does not exist");
    }

    public function unloadRelation(string $relation_name): void
    {
//        if (isset($this->attributes[$relation_name])) {
//            unset($this->attributes[$relation_name]);
//        }
    }

    abstract protected static function tableName(): string;

}