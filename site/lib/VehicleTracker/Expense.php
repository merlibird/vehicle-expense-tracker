<?php
declare(strict_types=1);

namespace VehicleTracker;

class Expense extends Entity {

    public function __construct(
        int $id,
        private readonly int $vehicleId,
        private readonly \DateTimeImmutable $date,
        private readonly float $cost,
        private readonly ?string $note,
        private readonly ?int $mileage,
        private readonly bool $isActive,
    ) {
        parent::__construct($id);
    }

    public function getVehicleId(): int           { return $this->vehicleId; }
    public function getDate(): \DateTimeImmutable { return $this->date; }
    public function getCost(): float              { return $this->cost; }
    public function getNote(): ?string            { return $this->note; }
    public function getMileage(): ?int            { return $this->mileage; }
    public function isActive(): bool              { return $this->isActive; }

}
