<?php
declare(strict_types=1);

namespace Data\Dao;

use Data\DatabaseConnection;
use Data\QueryRunner;
use VehicleTracker\Role;
use VehicleTracker\User;

class UserDao implements IUserDao {

    private readonly QueryRunner $runner;

    public function __construct(DatabaseConnection $connection) {
        $this->runner = new QueryRunner($connection);
    }

    public function getById(int $id): ?User {
        $res = $this->runner->run(
            'SELECT id, user_name, password_hash, first_name, last_name, profile_pic, role, is_active FROM user WHERE id = ?',
            [$id]
        );
        $row = $res->fetchObject();
        return $row ? $this->build($row) : null;
    }

    public function getAll(): array {
        $res   = $this->runner->run(
            'SELECT id, user_name, password_hash, first_name, last_name, profile_pic, role, is_active FROM user ORDER BY user_name'
        );
        $users = [];
        while ($row = $res->fetchObject()) {
            $users[] = $this->build($row);
        }
        return $users;
    }

    public function getByUserName(string $userName): ?User {
        $res = $this->runner->run(
            'SELECT id, user_name, password_hash, first_name, last_name, profile_pic, role, is_active FROM user WHERE user_name = ?',
            [$userName]
        );
        $row = $res->fetchObject();
        return $row ? $this->build($row) : null;
    }

    public function create(string $userName, string $passwordHash, string $firstName, string $lastName, ?string $profilePic, Role $role): int {
        $this->runner->run(
            'INSERT INTO user (user_name, password_hash, first_name, last_name, profile_pic, role, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)',
            [$userName, $passwordHash, $firstName, $lastName, $profilePic, $role->value]
        );
        return $this->runner->lastInsertId();
    }

    public function update(int $id, string $userName, string $firstName, string $lastName): void {
        $this->runner->run(
            'UPDATE user SET user_name = ?, first_name = ?, last_name = ? WHERE id = ?',
            [$userName, $firstName, $lastName, $id]
        );
    }

    public function updatePassword(int $id, string $passwordHash): void {
        $this->runner->run('UPDATE user SET password_hash = ? WHERE id = ?', [$passwordHash, $id]);
    }

    public function setActive(int $userId, bool $isActive): void {
        $this->runner->run('UPDATE user SET is_active = ? WHERE id = ?', [$isActive, $userId]);
    }

    private function build(object $row): User {
        return new User(
            (int)$row->id,
            $row->user_name,
            $row->password_hash,
            $row->first_name,
            $row->last_name,
            $row->profile_pic,
            Role::from($row->role),
            (bool)(int)$row->is_active,
        );
    }

}
