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

    /**
     * Categories for several expenses in a single query, grouped by expense id.
     * @param int[] $expenseIds
     * @return array<int, ExpenseCategory[]> expense id => its categories (expenses without categories are absent)
     */
    public function getByExpenseIds(array $expenseIds): array;

    public function setForExpense(int $expenseId, array $categoryIds): void;
}
