<?php
declare(strict_types=1);

namespace VehicleTracker;

class User extends Entity {

    public function __construct(
        int $id,
        private readonly string $userName,
        private readonly string $passwordHash,
        private readonly string $firstName,
        private readonly string $lastName,
        private readonly ?string $profilePic,
        private readonly Role $role,
        private readonly bool $isActive,
    ) {
        parent::__construct($id);
    }

    public function getUserName(): string    { return $this->userName; }
    public function getPasswordHash(): string { return $this->passwordHash; }
    public function getFirstName(): string   { return $this->firstName; }
    public function getLastName(): string    { return $this->lastName; }
    public function getProfilePic(): ?string { return $this->profilePic; }
    public function getRole(): Role          { return $this->role; }
    public function isActive(): bool         { return $this->isActive; }

    public function getFullName(): string {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function isAdmin(): bool {
        return $this->role === Role::Admin;
    }

}
