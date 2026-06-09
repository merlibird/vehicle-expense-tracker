<?php
declare(strict_types=1);

namespace Data\Dao;

use VehicleTracker\ExpenseCategory;

interface ICategoryDao {
    /** @return ExpenseCategory[] */
    public function getAll(): array;

    public function getByName(string $name): ?ExpenseCategory;

    /** @return ExpenseCategory[] */
    public function getByExpenseId(int $expenseId): array;

    public function setForExpense(int $expenseId, array $categoryIds): void;
}
