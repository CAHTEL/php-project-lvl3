<?php

namespace Hexlet\Code;

class Connection
{
    private static ?Connection $conn = null;

    public function connect()
    {
        $string = getenv('DATABASE_URL');
        $databaseUrl = parse_url((string) $string);
        $username = $databaseUrl['user'];
        $password = $databaseUrl['pass'];
        $host = $databaseUrl['host'];
        $port = $databaseUrl['port'];
        $dbName = ltrim($databaseUrl['path'], '/');
        $conStr = sprintf(
            "pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s",
            $host,
            $port,
            $dbName,
            $username,
            $password
        );
        $pdo = new \PDO($conStr);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }

    public static function get()
    {
        if (null === self::$conn) {
            self::$conn = new self();
        }

        return self::$conn;
    }

    protected function __construct()
    {
    }
}
