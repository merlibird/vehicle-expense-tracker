<?php
declare(strict_types=1);

namespace Data\Dao;

use Data\DatabaseConnection;
use Data\QueryRunner;
use VehicleTracker\Vehicle;

class VehicleDao implements IVehicleDao {

    private readonly QueryRunner $runner;

    public function __construct(DatabaseConnection $connection) {
        $this->runner = new QueryRunner($connection);
    }

    public function getByUserId(int $userId): array {
        $res      = $this->runner->run(
            'SELECT id, user_id, brand, model, license_plate, init_registration, is_active FROM vehicle WHERE user_id = ? AND is_active = 1 ORDER BY brand, model',
            [$userId]
        );
        $vehicles = [];
        while ($row = $res->fetchObject()) {
            $vehicles[] = $this->build($row);
        }
        return $vehicles;
    }

    public function getByUserIdPaged(int $userId, int $limit, int $offset): array {
        $res      = $this->runner->run(
            'SELECT id, user_id, brand, model, license_plate, init_registration, is_active FROM vehicle WHERE user_id = ? AND is_active = 1 ORDER BY brand, model LIMIT ? OFFSET ?',
            [$userId, $limit, $offset]
        );
        $vehicles = [];
        while ($row = $res->fetchObject()) {
            $vehicles[] = $this->build($row);
        }
        return $vehicles;
    }

    public function countByUserId(int $userId): int {
        $row = $this->runner->run(
            'SELECT COUNT(*) AS c FROM vehicle WHERE user_id = ? AND is_active = 1',
            [$userId]
        )->fetchObject();
        return (int)$row->c;
    }

    public function getById(int $id): ?Vehicle {
        $res = $this->runner->run(
            'SELECT id, user_id, brand, model, license_plate, init_registration, is_active FROM vehicle WHERE id = ?',
            [$id]
        );
        $row = $res->fetchObject();
        return $row ? $this->build($row) : null;
    }

    public function create(int $userId, string $brand, string $model, string $licensePlate, \DateTimeImmutable $initRegistration): int {
        $this->runner->run(
            'INSERT INTO vehicle (user_id, brand, model, license_plate, init_registration) VALUES (?, ?, ?, ?, ?)',
            [$userId, $brand, $model, $licensePlate, $initRegistration->format('Y-m-d')]
        );
        return $this->runner->lastInsertId();
    }

    public function update(int $id, string $brand, string $model, string $licensePlate, \DateTimeImmutable $initRegistration): void {
        $this->runner->run(
            'UPDATE vehicle SET brand = ?, model = ?, license_plate = ?, init_registration = ? WHERE id = ?',
            [$brand, $model, $licensePlate, $initRegistration->format('Y-m-d'), $id]
        );
    }

    public function softDelete(int $id): void {
        $this->runner->run('UPDATE vehicle SET is_active = 0 WHERE id = ?', [$id]);
    }

    private function build(object $row): Vehicle {
        return new Vehicle(
            (int)$row->id,
            (int)$row->user_id,
            $row->brand,
            $row->model,
            $row->license_plate,
            new \DateTimeImmutable($row->init_registration),
            (bool)(int)$row->is_active,
        );
    }

}
