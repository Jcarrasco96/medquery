<?php

declare(strict_types=1);

namespace app\core\database;

class TableUtils
{

    public static function columnDefinition(string $name, string $type, array $options): string
    {
        $defs = "`$name` $type";

        $defs .= ($options['null'] ?? false) ? ' NULL' : ' NOT NULL';

        if (array_key_exists('default', $options)) {
            $def = $options['default'];
            $defs .= ' DEFAULT ' . (is_string($def) ? self::quote($def) : $def);
        }

        if (!empty($options['auto_increment'])) {
            $defs .= ' AUTO_INCREMENT';
        }

        if (!empty($options['comment'])) {
            $defs .= ' COMMENT ' . self::quote($options['comment']);
        }

        return $defs;
    }

    private static function quote(string $value): string
    {
        return "'" . str_replace("'", "''", $value) . "'";
    }

}