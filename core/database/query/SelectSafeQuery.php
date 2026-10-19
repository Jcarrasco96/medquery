<?php

declare(strict_types=1);

namespace app\core\database\query;

use app\core\database\RawExpression;
use app\utils\debugger\collector\DatabaseCollector;
use InvalidArgumentException;
use PDO;

final class SelectSafeQuery extends SafeQuery
{

    private ?int $limit = null;
    private ?int $offset = null;
    private array $orderBy = [];
    private array $groupBy = [];

    public function data(array $data = ['*']): SelectSafeQuery
    {
        if (in_array('*', $data, true)) {
            $this->data = ['*'];
            return $this;
        }

        foreach ($data as $col) {
            if ($col instanceof RawExpression) {
                continue;
            }

            $this->validateIdentifier($col);
        }

//        foreach ($data as $col) {
//            $this->validateIdentifier($col);
//        }
        $this->data = $data;
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): SelectSafeQuery
    {
        $this->validateIdentifier($column);
        $direction = strtoupper($direction);
        if (!in_array($direction, ['ASC', 'DESC'], true)) {
            throw new InvalidArgumentException("Order by direction not valid: $direction");
        }
        $this->orderBy[] = "`$column` $direction";
        return $this;
    }

    public function groupBy(string $column): SelectSafeQuery
    {
        $this->validateIdentifier($column);
        $this->groupBy[] = "`$column`";
        return $this;
    }

    public function limit(int $limit): SelectSafeQuery
    {
        $this->limit = max(0, $limit);
        return $this;
    }

    public function offset(int $offset): SelectSafeQuery
    {
        $this->offset = max(0, $offset);
        return $this;
    }

    public function execute(): array
    {
        $start = hrtime(true);

        $sql = $this->getSql();

        $stmt = $this->pdo->prepare($sql);

        foreach ($this->params as $key => $val) {
            if (in_array($key, [':__offset', ':__limit'])) {
                $stmt->bindValue($key, $val, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $val);
            }
        }

        $stmt->execute();

        DatabaseCollector::record($sql, (hrtime(true) - $start) / 1_000_000, $this->params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function applyQueryParams(array $params): SelectSafeQuery
    {
        if (!empty($params['order'])) {
            $orders = explode(',', $params['order']);
            foreach ($orders as $order) {
                [$column, $direction] = array_map('trim', explode(':', $order) + [1 => 'asc']);
                $this->validateIdentifier($column);
                $dir = strtolower($direction) === 'desc' ? 'DESC' : 'ASC';
                $this->orderBy($column, $dir);
            }
        }

        if (isset($params['page'])) {
            if (!isset($params['limit'])) {
                $params['limit'] = 10;
            }

            $page = max(1, (int)$params['page']);
            $offset = (($params['limit']) * ($page - 1));
            $this->offset((int)$offset);
        }

        if (isset($params['limit'])) {
            $limit = $params['limit'];
            $this->limit(max(1, min($limit, 100)));
        }

        return $this;
    }

    public function exists(): bool
    {
        $start = hrtime(true);

        $this->validateTable();

        $sql = "SELECT 1 FROM `$this->table`";

        if (!empty($this->where)) {
            $sql .= ' WHERE ' . implode(' AND ', $this->where);
        }

        $sql .= ' LIMIT 1';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->params);

        DatabaseCollector::record($sql, (hrtime(true) - $start) / 1_000_000, $this->params);

        return (bool) $stmt->fetchColumn();
    }

    public function count(): int
    {
        $start = hrtime(true);

        $this->validateTable();

        $sql = "SELECT COUNT(*) FROM `$this->table`";

        if (!empty($this->where)) {
            $sql .= ' WHERE ' . implode(' AND ', $this->where);
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->params);

        DatabaseCollector::record($sql, (hrtime(true) - $start) / 1_000_000, $this->params);

        return (int) $stmt->fetchColumn();
    }

    public function prepareSQL(bool $bindParams = false): string
    {
        $sql = $this->getSql();

        if ($bindParams) {
            foreach ($this->params as $key => $val) {
                if (in_array($key, [':__offset', ':__limit'])) {
                    $sql = str_replace($key, $val, $sql);
                } else {
                    $sql = str_replace($key, '"$val"', $sql);
                }
            }
        }

        return $sql;
    }

    private function getSql(): string
    {
        $this->validateTable();
        $this->validateData();

        $sql = 'SELECT ';
        $sql .= $this->data === ['*']
            ? '*'
            : implode(', ', array_map(function ($col) {
                if ($col instanceof RawExpression) {
                    return $col->value;
                }

                return "`$col`";
            }, $this->data));
        $sql .= " FROM `$this->table`";

        if ($this->where) {
            $sql .= " WHERE " . implode(" AND ", $this->where);
        }
        if (!empty($this->groupBy)) {
            $sql .= " GROUP BY " . implode(', ', $this->groupBy);
        }
        if (!empty($this->orderBy)) {
            $sql .= " ORDER BY " . implode(', ', $this->orderBy);
        }
        if ($this->limit !== null) {
            $sql .= " LIMIT :__limit";
            $this->params[':__limit'] = $this->limit;
        }
        if ($this->offset !== null) {
            $sql .= " OFFSET :__offset";
            $this->params[':__offset'] = $this->offset;
        }
        return $sql;
    }

    public function raw(string $sql): RawExpression
    {
        return new RawExpression($sql);
    }

}