<?php
declare(strict_types=1);

namespace VehicleTracker;

class Util {

    /** Escapes a string for safe output in HTML (XSS protection). */
    public static function escape(?string $input): string {
        return htmlspecialchars($input ?? '', ENT_QUOTES, 'UTF-8');
    }

    public static function redirect(string $target): never {
        header('Location: ' . $target);
        exit();
    }

    public static function setError(string $message): void {
        $_SESSION['flash_errors'][] = $message;
    }

    /** @param string[] $messages */
    public static function setErrors(array $messages): void {
        foreach ($messages as $message) {
            self::setError($message);
        }
    }

    public static function setSuccess(string $message): void {
        $_SESSION['flash_success'][] = $message;
    }

    /**
     * Returns and clears all pending error messages.
     * @return string[]
     */
    public static function takeErrors(): array {
        $errors = $_SESSION['flash_errors'] ?? [];
        unset($_SESSION['flash_errors']);
        return $errors;
    }

    /**
     * Returns and clears all pending success messages.
     * @return string[]
     */
    public static function takeSuccess(): array {
        $messages = $_SESSION['flash_success'] ?? [];
        unset($_SESSION['flash_success']);
        return $messages;
    }

    /**
     * Stores submitted form values so a form can be repopulated after a
     * validation redirect. Never store passwords here.
     * @param array<string, string> $values
     */
    public static function setOld(array $values): void {
        $_SESSION['flash_old'] = $values;
    }

    /**
     * Returns and clears the stored form values.
     * @return array<string, string>
     */
    public static function takeOld(): array {
        $old = $_SESSION['flash_old'] ?? [];
        unset($_SESSION['flash_old']);
        return $old;
    }

    /** Parses a strict Y-m-d date; null if empty or invalid. */
    public static function parseDate(string $value): ?\DateTimeImmutable {
        if ($value === '') {
            return null;
        }
        $date   = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        $errors = \DateTimeImmutable::getLastErrors();
        if ($date === false || (is_array($errors) && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
            return null;
        }
        return $date;
    }

    /** Parses a decimal that may use a comma as separator; null if empty or invalid. */
    public static function parseDecimal(string $value): ?float {
        $value = str_replace(',', '.', trim($value));
        if ($value === '' || !is_numeric($value)) {
            return null;
        }
        return (float)$value;
    }

}
