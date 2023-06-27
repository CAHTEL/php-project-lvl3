<?php

namespace Hexlet\Code;

class Insert
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    public function insertLabel(string $name, string $created_at)
    {
        // подготовка запроса для добавления данных
        $sql = "INSERT INTO urls (name, created_at) VALUES(:name, :created_at)";
            $stmt = $this->pdo->prepare($sql);

            $stmt->bindValue(':name', $name);
            $stmt->bindValue(':created_at', $created_at);
            $stmt->execute();

        // возврат полученного значения id
        return $this->pdo->lastInsertId();
    }

    public function insertLabel2(string $url_id, $status_code, $h1, $title, $description, string $created_at)
    {
        // подготовка запроса для добавления данных
        $sql = "INSERT INTO url_checks (url_id, status_code, h1, title, description, created_at) VALUES(:url_id, :status_code, :h1, :title, :description, :created_at)";
            $stmt = $this->pdo->prepare($sql);

            $stmt->bindValue(':url_id', $url_id);
            $stmt->bindValue(':status_code', $status_code);
            $stmt->bindValue(':h1', $h1);
            $stmt->bindValue(':title', $title);
            $stmt->bindValue(':description', $description);
            $stmt->bindValue(':created_at', $created_at);
            $stmt->execute();

        // возврат полученного значения id
        return $this->pdo->lastInsertId();
    }

    
}