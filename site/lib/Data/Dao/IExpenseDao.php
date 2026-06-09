<?php
declare(strict_types=1);

namespace Data\Dao;

use VehicleTracker\Expense;

interface IExpenseDao {
    /** @return Expense[] */
    public function getByVehicleId(int $vehicleId): array;

    /**
     * Active expenses for several vehicles in a single query, grouped by vehicle id.
     * @param int[] $vehicleIds
     * @return array<int, Expense[]> vehicle id => its expenses (vehicles without expenses are absent)
     */
    public function getByVehicleIds(array $vehicleIds): array;

    /** @return Expense[] */
    public function getByUserIdFiltered(int $userId, ?int $vehicleId, ?int $year, ?int $month, ?int $categoryId): array;

    /** @return Expense[] */
    public function getByUserIdFilteredPaged(int $userId, ?int $vehicleId, ?int $year, ?int $month, ?int $categoryId, int $limit, int $offset): array;

    public function countByUserIdFiltered(int $userId, ?int $vehicleId, ?int $year, ?int $month, ?int $categoryId): int;

    public function getTotalByUser(int $userId, ?int $year, ?int $month, ?int $vehicleId): float;

    /** @return array<int, array{name: string, total: float}> */
    public function getCategoryTotalsByUser(int $userId, ?int $year, ?int $month, ?int $vehicleId): array;

    /** @return int[] */
    public function getActiveMonths(int $userId, int $year, ?int $vehicleId): array;

    /** @return int[] */
    public function getYearsByUser(int $userId): array;

    public function getById(int $id): ?Expense;
    public function create(int $vehicleId, \DateTimeImmutable $date, float $cost, ?string $note, ?int $mileage): int;
    public function createFuel(int $vehicleId, \DateTimeImmutable $date, float $cost, ?string $note, ?int $mileage, float $liters, float $pricePerLiter): int;
    public function update(int $id, \DateTimeImmutable $date, float $cost, ?string $note, ?int $mileage): void;
    public function updateFuel(int $id, \DateTimeImmutable $date, float $cost, ?string $note, ?int $mileage, float $liters, float $pricePerLiter): void;
    public function softDelete(int $id): void;
}
