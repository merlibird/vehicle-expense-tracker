<?php
declare(strict_types=1);

namespace VehicleTracker;

use Data\Dao\IUserDao;
use Data\Dao\ILogDao;

class AuthenticationManager {

    public function __construct(
        private readonly IUserDao $userDao,
        private readonly ILogDao  $logDao,
    ) {}

    public function login(string $userName, string $password): bool {
        $ip   = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user = $this->userDao->getByUserName($userName);

        if ($user === null || !password_verify($password, $user->getPasswordHash())) {
            $this->logDao->create(null, $userName, $ip, 'LOGIN_FAILED');
            return false;
        }

        if (!$user->isActive()) {
            $this->logDao->create($user->getId(), $userName, $ip, 'LOGIN_DENIED_INACTIVE');
            return false;
        }

        SessionContext::setUserId($user->getId());
        $this->logDao->create($user->getId(), $userName, $ip, 'LOGIN_SUCCESS');
        return true;
    }

    public function register(string $userName, string $password, string $firstName, string $lastName): ?int {
        if ($this->userDao->getByUserName($userName) !== null) {
            return null;
        }

        $hash   = password_hash($password, PASSWORD_DEFAULT);
        $userId = $this->userDao->create($userName, $hash, $firstName, $lastName, Role::User);

        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $this->logDao->create($userId, $userName, $ip, 'REGISTER_SUCCESS');
        return $userId;
    }

    public function changePassword(int $userId, string $currentPassword, string $newPassword): bool {
        $user = $this->userDao->getById($userId);
        if ($user === null || !password_verify($currentPassword, $user->getPasswordHash())) {
            return false;
        }

        $this->userDao->updatePassword($userId, password_hash($newPassword, PASSWORD_DEFAULT));

        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $this->logDao->create($userId, $user->getUserName(), $ip, 'PASSWORD_CHANGE');
        return true;
    }

    public function logout(): void {
        $user = $this->getCurrentUser();
        if ($user !== null) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $this->logDao->create($user->getId(), $user->getUserName(), $ip, 'LOGOUT');
        }
        SessionContext::clear();
    }

    public function isLoggedIn(): bool {
        return SessionContext::hasUser();
    }

    public function getCurrentUser(): ?User {
        if ($this->isLoggedIn()) {
            $userId = SessionContext::getUserId();
            return $this->userDao->getById($userId);
        }
        return null;
    }

    public function requireUser(): User {
        $user = $this->getCurrentUser();
        if ($user === null) {
            Util::redirect('index.php?view=login');
        }
        return $user;
    }

    public function requireAdmin(): User {
        $user = $this->requireUser();
        if (!$user->isAdmin()) {
            Util::redirect('index.php?view=dashboard');
        }
        return $user;
    }

}
