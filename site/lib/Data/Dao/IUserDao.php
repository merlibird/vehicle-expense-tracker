<?php
declare(strict_types=1);

namespace Data\Dao;

use VehicleTracker\Role;
use VehicleTracker\User;

interface IUserDao {
    /** @return User[] all users (incl. inactive), for admin management */
    public function getAll(): array;
    public function getById(int $id): ?User;
    public function getByUserName(string $userName): ?User;
    public function create(string $userName, string $passwordHash, string $firstName, string $lastName, ?string $profilePic, Role $role): int;
    public function update(int $id, string $userName, string $firstName, string $lastName): void;
    public function updatePassword(int $id, string $passwordHash): void;
    public function setActive(int $userId, bool $isActive): void;
}
