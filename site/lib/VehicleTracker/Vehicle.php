<?php
declare(strict_types=1);

namespace VehicleTracker;

class Vehicle extends Entity {

    public function __construct(
        int $id,
        private readonly int $userId,
        private readonly string $brand,
        private readonly string $model,
        private readonly string $licensePlate,
        private readonly \DateTimeImmutable $initRegistration,
        private readonly bool $isActive,
    ) {
        parent::__construct($id);
    }

    public function getUserId(): int                        { return $this->userId; }
    public function getBrand(): string                      { return $this->brand; }
    public function getModel(): string                      { return $this->model; }
    public function getLicensePlate(): string               { return $this->licensePlate; }
    public function getInitRegistration(): \DateTimeImmutable { return $this->initRegistration; }
    public function isActive(): bool                        { return $this->isActive; }

    public function getDisplayName(): string {
        return $this->brand . ' ' . $this->model;
    }

}
