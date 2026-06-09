<?php
declare(strict_types=1);

namespace Data\Dao;

use VehicleTracker\LogEntry;

interface ILogDao {
    public function create(?int $userId, string $username, string $ipAddress, string $action): void;

    /** @return LogEntry[] */
    public function getAll(): array;
}
