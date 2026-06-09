<?php
declare(strict_types=1);

namespace VehicleTracker\Controller;

/**
 * Shared vehicle-ownership check. Expects the using class to expose an
 * IVehicleDao as $this->vehicleDao.
 */
trait VehicleOwnership {

    /** True if the vehicle exists, is active and belongs to the given user. */
    protected function ownsVehicle(int $vehicleId, int $userId): bool {
        $vehicle = $this->vehicleDao->getById($vehicleId);
        return $vehicle !== null && $vehicle->isActive() && $vehicle->getUserId() === $userId;
    }
}
