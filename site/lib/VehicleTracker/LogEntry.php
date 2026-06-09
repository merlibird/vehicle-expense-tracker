<?php
declare(strict_types=1);

namespace VehicleTracker;

class LogEntry extends Entity {

    public function __construct(
        int $id,
        private readonly ?int $userId,
        private readonly string $username,
        private readonly string $ipAddress,
        private readonly string $action,
        private readonly \DateTimeImmutable $timestamp,
    ) {
        parent::__construct($id);
    }

    public function getUserId(): ?int                   { return $this->userId; }
    public function getUsername(): string               { return $this->username; }
    public function getIpAddress(): string              { return $this->ipAddress; }
    public function getAction(): string                 { return $this->action; }
    public function getTimestamp(): \DateTimeImmutable  { return $this->timestamp; }

}
