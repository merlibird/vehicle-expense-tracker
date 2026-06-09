<?php
declare(strict_types=1);

namespace Data\Dao;

use Data\DatabaseConnection;
use Data\QueryRunner;
use VehicleTracker\ExpenseCategory;

class CategoryDao implements ICategoryDao {

    private readonly QueryRunner $runner;

    public function __construct(DatabaseConnection $connection) {
        $this->runner = new QueryRunner($connection);
    }

    public function getAll(): array {
        $res        = $this->runner->run('SELECT id, name FROM expense_category ORDER BY name');
        $categories = [];
        while ($row = $res->fetchObject()) {
            $categories[] = new ExpenseCategory((int)$row->id, $row->name);
        }
        return $categories;
    }

    public function getByName(string $name): ?ExpenseCategory {
        $res = $this->runner->run('SELECT id, name FROM expense_category WHERE name = ?', [$name]);
        $row = $res->fetchObject();
        return $row ? new ExpenseCategory((int)$row->id, $row->name) : null;
    }

    public function getByExpenseId(int $expenseId): array {
        $res        = $this->runner->run(
            'SELECT c.id, c.name FROM expense_category c JOIN expense_category_map m ON c.id = m.category_id WHERE m.expense_id = ? ORDER BY c.name',
            [$expenseId]
        );
        $categories = [];
        while ($row = $res->fetchObject()) {
            $categories[] = new ExpenseCategory((int)$row->id, $row->name);
        }
        return $categories;
    }

    public function setForExpense(int $expenseId, array $categoryIds): void {
        $this->runner->beginTransaction();
        try {
            $this->runner->run('DELETE FROM expense_category_map WHERE expense_id = ?', [$expenseId]);
            foreach ($categoryIds as $categoryId) {
                $this->runner->run(
                    'INSERT INTO expense_category_map (expense_id, category_id) VALUES (?, ?)',
                    [$expenseId, (int)$categoryId]
                );
            }
            $this->runner->commit();
        } catch (\Exception $e) {
            $this->runner->rollBack();
            throw $e;
        }
    }

}
