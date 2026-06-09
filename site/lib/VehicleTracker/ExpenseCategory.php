<?php
declare(strict_types=1);

namespace VehicleTracker;

class ExpenseCategory extends Entity {

    public function __construct(
        int $id,
        private readonly string $name,
    ) {
        parent::__construct($id);
    }

    public function getName(): string { return $this->name; }

}
