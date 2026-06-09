<?php
declare(strict_types=1);

namespace Data\Dao;

use VehicleTracker\Vehicle;

interface IVehicleDao {
    /** @return Vehicle[] */
    public function getByUserId(int $userId): array;

    /** @return Vehicle[] one page of the user's active vehicles */
    public function getByUserIdPaged(int $userId, int $limit, int $offset): array;

    /** Total number of the user's active vehicles. */
    public function countByUserId(int $userId): int;

    public function getById(int $id): ?Vehicle;
    public function create(int $userId, string $brand, string $model, string $licensePlate, \DateTimeImmutable $initRegistration): int;
    public function update(int $id, string $brand, string $model, string $licensePlate, \DateTimeImmutable $initRegistration): void;
    public function softDelete(int $id): void;
}
