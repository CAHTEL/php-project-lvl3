<?php

namespace Hexlet\Code;

class Select
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function select()
    {
        $sql = "SELECT * FROM urls ORDER BY id DESC";
        $stmt = $this->pdo->query($sql);

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

    public function selectId($id)
    {
        $sql = "SELECT * FROM urls WHERE id = $id";
        $stmt = $this->pdo->query($sql);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function selectId2($id)
    {
        $sql = "SELECT * FROM url_checks WHERE url_id = $id";
        $stmt = $this->pdo->query($sql);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}