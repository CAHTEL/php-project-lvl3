<?php

namespace Hexlet\Code;

class Insert
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function insertSql(string $sql, array $val)
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($val);
        return $this->pdo->lastInsertId();
    }
}
