<?php
declare(strict_types=1);

namespace VehicleTracker;

abstract class Entity {

    public function __construct(
        private readonly int $id
    ) {}

    public function getId(): int {
        return $this->id;
    }

}
