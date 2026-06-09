<?php
declare(strict_types=1);

namespace Data\Dao;

use VehicleTracker\Expense;

interface IExpenseDao {
    /** @return Expense[] */
    public function getByVehicleId(int $vehicleId): array;

    /**
     * Active expenses across the user's active vehicles, optionally filtered.
     * Any filter passed as null is ignored.
     * @return Expense[]
     */
    public function getByUserIdFiltered(int $userId, ?int $vehicleId, ?int $year, ?int $month, ?int $categoryId): array;

    /**
     * One page of {@see getByUserIdFiltered()} (same filters, plus LIMIT/OFFSET).
     * @return Expense[]
     */
    public function getByUserIdFilteredPaged(int $userId, ?int $vehicleId, ?int $year, ?int $month, ?int $categoryId, int $limit, int $offset): array;

    /** Total number of expenses matching {@see getByUserIdFiltered()}. */
    public function countByUserIdFiltered(int $userId, ?int $vehicleId, ?int $year, ?int $month, ?int $categoryId): int;

    /** Sum of all matching expense costs (any filter null is ignored). */
    public function getTotalByUser(int $userId, ?int $year, ?int $month, ?int $vehicleId): float;

    /**
     * Cost summed per category for the period.
     * @return array<int, array{name: string, total: float}>
     */
    public function getCategoryTotalsByUser(int $userId, ?int $year, ?int $month, ?int $vehicleId): array;

    /** @return int[] months (1-12) that have expenses in the given year, optionally for one vehicle */
    public function getActiveMonths(int $userId, int $year, ?int $vehicleId): array;

    /** @return int[] distinct years that have expenses, newest first */
    public function getYearsByUser(int $userId): array;

    public function getById(int $id): ?Expense;
    public function create(int $vehicleId, \DateTimeImmutable $date, float $cost, ?string $note, ?int $mileage): int;
    public function createFuel(int $vehicleId, \DateTimeImmutable $date, float $cost, ?string $note, ?int $mileage, float $liters, float $pricePerLiter): int;
    public function update(int $id, \DateTimeImmutable $date, float $cost, ?string $note, ?int $mileage): void;
    public function updateFuel(int $id, \DateTimeImmutable $date, float $cost, ?string $note, ?int $mileage, float $liters, float $pricePerLiter): void;
    public function softDelete(int $id): void;
}
