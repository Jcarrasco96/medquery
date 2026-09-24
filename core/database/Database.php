<?php

declare(strict_types=1);

namespace app\core\database;

use PDO;
use PDOException;

final class Database
{

    private static PDO $pdo;

    public static function load(array $databaseConfig): PDO
    {
        if (!isset(self::$pdo)) {
            $config = $databaseConfig[$databaseConfig['driver']];

            switch ($databaseConfig['driver']) {
                case 'mysql':
                    self::loadMySqlDriver($config['host'], intval($config['port']), $config['dbname'], $config['user'], $config['password'], $config['charset']);
                    break;

                case 'sqlsrv':
                    self::loadSqlSrvDriver($config['host'], intval($config['port']), $config['dbname'], $config['user'], $config['password']);
                    break;

                case 'pgsql':
                    self::loadPgSqlDriver($config['host'], intval($config['port']), $config['dbname'], $config['user'], $config['password']);
                    break;

                case 'sqlite':
                    self::loadSqliteDriver($config['path']);
                    break;

                default:
                    throw new PDOException("Database connection error");
            }

            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        return self::$pdo;
    }

    private static function loadMySqlDriver(string $host, int $port, string $dbname, string $user, string $password, string $charset): void
    {
        self::$pdo = new PDO("mysql:host=$host:$port;dbname=$dbname;charset=$charset", $user, $password, [PDO::ATTR_PERSISTENT => true]);
    }

    private static function loadSqlSrvDriver(string $host, int $port, string $dbname, string $user, string $password): void
    {
        self::$pdo = new PDO("sqlsrv:Server=$host:$port;Database=$dbname", $user, $password, [PDO::ATTR_PERSISTENT => true]);
    }

    private static function loadPgSqlDriver(string $host, int $port, string $dbname, string $user, string $password): void
    {
        self::$pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname;user=$user;password=$password");
    }

    private static function loadSqliteDriver(string $path): void
    {
        self::$pdo = new PDO("sqlite:$path", null, null, [PDO::ATTR_PERSISTENT => true]);
    }

}