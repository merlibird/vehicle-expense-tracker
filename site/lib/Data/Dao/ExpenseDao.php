<?php
declare(strict_types=1);

namespace Data\Dao;

use Data\DatabaseConnection;
use Data\QueryRunner;
use VehicleTracker\Expense;
use VehicleTracker\FuelExpense;

class ExpenseDao implements IExpenseDao {

    private readonly QueryRunner $runner;

    public function __construct(DatabaseConnection $connection) {
        $this->runner = new QueryRunner($connection);
    }

    public function getByVehicleId(int $vehicleId): array {
        $res      = $this->runner->run(
            'SELECT id, vehicle_id, date, cost, note, mileage, is_active, is_fuel_expense, liters, price_per_liter FROM expense WHERE vehicle_id = ? AND is_active = 1 ORDER BY date DESC',
            [$vehicleId]
        );
        $expenses = [];
        while ($row = $res->fetchObject()) {
            $expenses[] = $this->build($row);
        }
        return $expenses;
    }

    public function getByUserIdFiltered(int $userId, ?int $vehicleId, ?int $year, ?int $month, ?int $categoryId): array {
        [$fromWhere, $params] = $this->buildFilter($userId, $vehicleId, $year, $month, $categoryId);
        $sql = 'SELECT DISTINCT e.id, e.vehicle_id, e.date, e.cost, e.note, e.mileage, e.is_active, e.is_fuel_expense, e.liters, e.price_per_liter '
             . $fromWhere . ' ORDER BY e.date DESC, e.id DESC';

        $res      = $this->runner->run($sql, $params);
        $expenses = [];
        while ($row = $res->fetchObject()) {
            $expenses[] = $this->build($row);
        }
        return $expenses;
    }

    public function getByUserIdFilteredPaged(int $userId, ?int $vehicleId, ?int $year, ?int $month, ?int $categoryId, int $limit, int $offset): array {
        [$fromWhere, $params] = $this->buildFilter($userId, $vehicleId, $year, $month, $categoryId);
        $sql      = 'SELECT DISTINCT e.id, e.vehicle_id, e.date, e.cost, e.note, e.mileage, e.is_active, e.is_fuel_expense, e.liters, e.price_per_liter '
                  . $fromWhere . ' ORDER BY e.date DESC, e.id DESC LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;

        $res      = $this->runner->run($sql, $params);
        $expenses = [];
        while ($row = $res->fetchObject()) {
            $expenses[] = $this->build($row);
        }
        return $expenses;
    }

    public function countByUserIdFiltered(int $userId, ?int $vehicleId, ?int $year, ?int $month, ?int $categoryId): int {
        [$fromWhere, $params] = $this->buildFilter($userId, $vehicleId, $year, $month, $categoryId);
        $row = $this->runner->run('SELECT COUNT(DISTINCT e.id) AS c ' . $fromWhere, $params)->fetchObject();
        return (int)$row->c;
    }

    /**
     * Builds the shared "FROM … WHERE …" clause + bound params for the filtered
     * queries (list, paged list, count) so the filter logic lives in one place.
     * @return array{0: string, 1: array}
     */
    private function buildFilter(int $userId, ?int $vehicleId, ?int $year, ?int $month, ?int $categoryId): array {
        $sql = 'FROM expense e JOIN vehicle v ON e.vehicle_id = v.id ';
        if ($categoryId !== null) {
            $sql .= 'JOIN expense_category_map m ON m.expense_id = e.id ';
        }
        $sql   .= 'WHERE v.user_id = ? AND e.is_active = 1 AND v.is_active = 1';
        $params = [$userId];
        if ($vehicleId !== null) { $sql .= ' AND e.vehicle_id = ?';  $params[] = $vehicleId; }
        if ($year !== null)      { $sql .= ' AND YEAR(e.date) = ?';  $params[] = $year; }
        if ($month !== null)     { $sql .= ' AND MONTH(e.date) = ?'; $params[] = $month; }
        if ($categoryId !== null){ $sql .= ' AND m.category_id = ?'; $params[] = $categoryId; }
        return [$sql, $params];
    }

    /** Sum of all matching expense costs (optionally filtered by year/month/vehicle). */
    public function getTotalByUser(int $userId, ?int $year, ?int $month, ?int $vehicleId): float {
        $sql    = 'SELECT COALESCE(SUM(e.cost), 0) AS total FROM expense e JOIN vehicle v ON e.vehicle_id = v.id '
                . 'WHERE v.user_id = ? AND e.is_active = 1 AND v.is_active = 1';
        $params = [$userId];
        if ($vehicleId !== null) { $sql .= ' AND e.vehicle_id = ?';  $params[] = $vehicleId; }
        if ($year !== null)      { $sql .= ' AND YEAR(e.date) = ?';  $params[] = $year; }
        if ($month !== null)     { $sql .= ' AND MONTH(e.date) = ?'; $params[] = $month; }

        $row = $this->runner->run($sql, $params)->fetchObject();
        return (float)$row->total;
    }

    /**
     * Cost summed per category for the period. Multi-category expenses count
     * toward each of their categories.
     * @return array<int, array{name: string, total: float}>
     */
    public function getCategoryTotalsByUser(int $userId, ?int $year, ?int $month, ?int $vehicleId): array {
        $sql    = 'SELECT c.name AS name, SUM(e.cost) AS total '
                . 'FROM expense e JOIN vehicle v ON e.vehicle_id = v.id '
                . 'JOIN expense_category_map m ON m.expense_id = e.id '
                . 'JOIN expense_category c ON c.id = m.category_id '
                . 'WHERE v.user_id = ? AND e.is_active = 1 AND v.is_active = 1';
        $params = [$userId];
        if ($vehicleId !== null) { $sql .= ' AND e.vehicle_id = ?';  $params[] = $vehicleId; }
        if ($year !== null)      { $sql .= ' AND YEAR(e.date) = ?';  $params[] = $year; }
        if ($month !== null)     { $sql .= ' AND MONTH(e.date) = ?'; $params[] = $month; }
        $sql   .= ' GROUP BY name ORDER BY total DESC';

        $res  = $this->runner->run($sql, $params);
        $rows = [];
        while ($row = $res->fetchObject()) {
            $rows[] = ['name' => $row->name, 'total' => (float)$row->total];
        }
        return $rows;
    }

    /** @return int[] months (1-12) that have expenses in the given year, optionally for one vehicle */
    public function getActiveMonths(int $userId, int $year, ?int $vehicleId): array {
        $sql    = 'SELECT DISTINCT MONTH(e.date) AS m FROM expense e JOIN vehicle v ON e.vehicle_id = v.id '
                . 'WHERE v.user_id = ? AND e.is_active = 1 AND v.is_active = 1 AND YEAR(e.date) = ?';
        $params = [$userId, $year];
        if ($vehicleId !== null) { $sql .= ' AND e.vehicle_id = ?'; $params[] = $vehicleId; }
        $sql   .= ' ORDER BY m';

        $res    = $this->runner->run($sql, $params);
        $months = [];
        while ($row = $res->fetchObject()) {
            $months[] = (int)$row->m;
        }
        return $months;
    }

    /** @return int[] distinct years that have expenses, newest first */
    public function getYearsByUser(int $userId): array {
        $res   = $this->runner->run(
            'SELECT DISTINCT YEAR(e.date) AS y FROM expense e JOIN vehicle v ON e.vehicle_id = v.id '
            . 'WHERE v.user_id = ? AND e.is_active = 1 AND v.is_active = 1 ORDER BY y DESC',
            [$userId]
        );
        $years = [];
        while ($row = $res->fetchObject()) {
            $years[] = (int)$row->y;
        }
        return $years;
    }

    public function getById(int $id): ?Expense {
        $res = $this->runner->run(
            'SELECT id, vehicle_id, date, cost, note, mileage, is_active, is_fuel_expense, liters, price_per_liter FROM expense WHERE id = ?',
            [$id]
        );
        $row = $res->fetchObject();
        return $row ? $this->build($row) : null;
    }

    public function create(int $vehicleId, \DateTimeImmutable $date, float $cost, ?string $note, ?int $mileage): int {
        $this->runner->run(
            'INSERT INTO expense (vehicle_id, date, cost, note, mileage, is_fuel_expense) VALUES (?, ?, ?, ?, ?, 0)',
            [$vehicleId, $date->format('Y-m-d'), $cost, $note, $mileage]
        );
        return $this->runner->lastInsertId();
    }

    public function createFuel(int $vehicleId, \DateTimeImmutable $date, float $cost, ?string $note, ?int $mileage, float $liters, float $pricePerLiter): int {
        $this->runner->run(
            'INSERT INTO expense (vehicle_id, date, cost, note, mileage, is_fuel_expense, liters, price_per_liter) VALUES (?, ?, ?, ?, ?, 1, ?, ?)',
            [$vehicleId, $date->format('Y-m-d'), $cost, $note, $mileage, $liters, $pricePerLiter]
        );
        return $this->runner->lastInsertId();
    }

    public function update(int $id, \DateTimeImmutable $date, float $cost, ?string $note, ?int $mileage): void {
        $this->runner->run(
            'UPDATE expense SET date = ?, cost = ?, note = ?, mileage = ? WHERE id = ?',
            [$date->format('Y-m-d'), $cost, $note, $mileage, $id]
        );
    }

    public function updateFuel(int $id, \DateTimeImmutable $date, float $cost, ?string $note, ?int $mileage, float $liters, float $pricePerLiter): void {
        $this->runner->run(
            'UPDATE expense SET date = ?, cost = ?, note = ?, mileage = ?, liters = ?, price_per_liter = ? WHERE id = ?',
            [$date->format('Y-m-d'), $cost, $note, $mileage, $liters, $pricePerLiter, $id]
        );
    }

    public function softDelete(int $id): void {
        $this->runner->run('UPDATE expense SET is_active = 0 WHERE id = ?', [$id]);
    }

    private function build(object $row): Expense {
        $id        = (int)$row->id;
        $vehicleId = (int)$row->vehicle_id;
        $date      = new \DateTimeImmutable($row->date);
        $cost      = (float)$row->cost;
        $note      = $row->note;
        $mileage   = $row->mileage !== null ? (int)$row->mileage : null;
        $isActive  = (bool)(int)$row->is_active;

        if ((bool)(int)$row->is_fuel_expense) {
            return new FuelExpense($id, $vehicleId, $date, $cost, $note, $mileage, $isActive, (float)$row->liters, (float)$row->price_per_liter);
        }
        return new Expense($id, $vehicleId, $date, $cost, $note, $mileage, $isActive);
    }

}
