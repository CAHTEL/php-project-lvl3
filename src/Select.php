<?php

namespace Hexlet\Code;

class Select
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function select($url)
    {
        $sql = "SELECT * FROM urls WHERE name = ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$url]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);

    }

    public function selectSql($sql)
    {
        $stmt = $this->pdo->query($sql);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);

    }

    public function selectChecks()
    {
        $sql = "SELECT * FROM url_checks";
        $stmt = $this->pdo->query($sql);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);

    }

    public function selectTime($time)
    {
        $sql = "SELECT * FROM urls WHERE created_at = '$time'";
        $stmt = $this->pdo->query($sql);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}