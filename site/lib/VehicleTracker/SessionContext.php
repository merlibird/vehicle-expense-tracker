<?php
declare(strict_types=1);

namespace VehicleTracker;

class SessionContext {

    private const string KEY_USER_ID = 'user_id';
    private static $exists = false;

    public static function start(): void {
        if (!self::$exists) {
            session_start();
            self::$exists = true;
        }
    }

    public static function setUserId(int $userId): void {
        $_SESSION[self::KEY_USER_ID] = $userId;
    }

    public static function getUserId(): ?int {
        return isset($_SESSION[self::KEY_USER_ID]) ? (int)$_SESSION[self::KEY_USER_ID] : null;
    }

    public static function hasUser(): bool {
        return isset($_SESSION[self::KEY_USER_ID]);
    }

    public static function clear(): void {
        unset($_SESSION[self::KEY_USER_ID]);
    }

}
