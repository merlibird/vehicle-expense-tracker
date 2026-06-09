<?php
declare(strict_types=1);

namespace Data;

class DatabaseConnection {

    private ?\PDO $pdo = null;

    public function __construct(
        private readonly string $dsn,
        private readonly string $user,
        private readonly string $password,
    ) {}

    public function getConnection(): \PDO {
        if ($this->pdo === null) {
            $this->pdo = new \PDO($this->dsn, $this->user, $this->password);
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }
        return $this->pdo;
    }

}
