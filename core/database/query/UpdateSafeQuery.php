<?php

declare(strict_types=1);

namespace app\core\database\query;

class UpdateSafeQuery extends SafeQuery
{

    public function execute(): bool
    {
        $this->validateTable();
        $this->validateWhere();
        $this->validateData();

        $sets = [];
        foreach ($this->data as $col => $val) {
            $param = ":u_" . $col;
            $sets[] = "`$col` = $param";
            $this->params[$param] = $val;
        }
        $sql = "UPDATE `$this->table` SET " . implode(", ", $sets) . " WHERE " . implode(" AND ", $this->where);

        $stmt = $this->pdo->prepare($sql);
        foreach ($this->params as $key => $val) {
//            $param = is_numeric($val) ? PDO::PARAM_INT : PDO::PARAM_STR;
//            $stmt->bindValue($key, $val, $param);
            $stmt->bindValue($key, $val);
        }

        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

}