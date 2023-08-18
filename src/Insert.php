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

    public function insertLabel2(mixed $url, mixed $s_code, mixed $h1, mixed $tit, mixed $desc, mixed $cr_at)
    {
        $sql = "INSERT INTO url_checks (url_id, status_code, h1, title, description, created_at) VALUES(:url,
        :s_code, :h1, :tit, :desc, :cr_at)";
            $stmt = $this->pdo->prepare($sql);

            $stmt->bindValue(':url', $url);
            $stmt->bindValue(':s_code', $s_code);
            $stmt->bindValue(':h1', $h1);
            $stmt->bindValue(':tit', $tit);
            $stmt->bindValue(':desc', $desc);
            $stmt->bindValue(':cr_at', $cr_at);
            $stmt->execute();
            return $this->pdo->lastInsertId();
    }
}
