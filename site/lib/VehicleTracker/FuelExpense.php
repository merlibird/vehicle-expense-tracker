<?php
declare(strict_types=1);

namespace VehicleTracker;

class FuelExpense extends Expense {

    public function __construct(
        int $id,
        int $vehicleId,
        \DateTimeImmutable $date,
        float $cost,
        ?string $note,
        ?int $mileage,
        bool $isActive,
        private readonly float $liters,
        private readonly float $pricePerLiter,
    ) {
        parent::__construct($id, $vehicleId, $date, $cost, $note, $mileage, $isActive);
    }

    public function getLiters(): float        { return $this->liters; }
    public function getPricePerLiter(): float { return $this->pricePerLiter; }

    public function getTotalFuelCost(): float {
        return round($this->liters * $this->pricePerLiter, 2);
    }

}
