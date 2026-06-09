<?php
declare(strict_types=1);

namespace Data;

class QueryRunner {

    public function __construct(private readonly DatabaseConnection $connection) {}

    public function run(string $sql, array $params = []): \PDOStatement {
        $pdo  = $this->connection->getConnection();
        $stmt = $pdo->prepare($sql);
        $i    = 1;
        foreach ($params as $param) {
            match(true) {
                $param === null => $stmt->bindValue($i, null, \PDO::PARAM_NULL),
                is_bool($param) => $stmt->bindValue($i, $param, \PDO::PARAM_BOOL),
                is_int($param)  => $stmt->bindValue($i, $param, \PDO::PARAM_INT),
                default         => $stmt->bindValue($i, (string)$param, \PDO::PARAM_STR),
            };
            $i++;
        }
        $stmt->execute();
        return $stmt;
    }

    public function lastInsertId(): int {
        return (int)$this->connection->getConnection()->lastInsertId();
    }

    public function beginTransaction(): void {
        $this->connection->getConnection()->beginTransaction();
    }

    public function commit(): void {
        $this->connection->getConnection()->commit();
    }

    public function rollBack(): void {
        $this->connection->getConnection()->rollBack();
    }

}
