<?php

namespace Hexlet\Code;

class Select
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function selectSql(string $sql, array $val = null)
    {
        if ($val !== null) {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($val);
        } else {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
        }
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
