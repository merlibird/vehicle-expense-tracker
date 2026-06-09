<?php
declare(strict_types=1);

namespace Data\Dao;

use Data\DatabaseConnection;
use Data\QueryRunner;
use VehicleTracker\LogEntry;

class LogDao implements ILogDao {

    private readonly QueryRunner $runner;

    public function __construct(DatabaseConnection $connection) {
        $this->runner = new QueryRunner($connection);
    }

    public function create(?int $userId, string $username, string $ipAddress, string $action): void {
        $this->runner->run(
            'INSERT INTO log_entry (user_id, username, ip_address, action) VALUES (?, ?, ?, ?)',
            [$userId, $username, $ipAddress, $action]
        );
    }

    public function getAll(): array {
        $res     = $this->runner->run('SELECT id, user_id, username, ip_address, action, time_stamp FROM log_entry ORDER BY time_stamp DESC');
        $entries = [];
        while ($row = $res->fetchObject()) {
            $entries[] = new LogEntry(
                (int)$row->id,
                $row->user_id !== null ? (int)$row->user_id : null,
                $row->username,
                $row->ip_address,
                $row->action,
                new \DateTimeImmutable($row->time_stamp),
            );
        }
        return $entries;
    }

}
