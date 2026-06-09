<?php
declare(strict_types=1);

namespace VehicleTracker\Controller;

use VehicleTracker\Util;

class AuthController extends AbstractController {

    public const string ACTION_LOGIN    = 'login';
    public const string ACTION_LOGOUT   = 'logout';
    public const string ACTION_REGISTER = 'register';

    public function handles(string $action): bool {
        return in_array($action, [self::ACTION_LOGIN, self::ACTION_LOGOUT, self::ACTION_REGISTER], true);
    }

    public function dispatch(string $action): void {
        match ($action) {
            self::ACTION_LOGIN    => $this->login(),
            self::ACTION_LOGOUT   => $this->logout(),
            self::ACTION_REGISTER => $this->register(),
        };
    }

    private function login(): never {
        $userName = trim($_POST['userName'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($userName === '' || $password === '') {
            Util::setError('Bitte Benutzername und Passwort eingeben.');
            Util::setOld(['userName' => $userName]);
            Util::redirect('index.php?view=login');
        }

        if (!$this->auth->login($userName, $password)) {
            Util::setError('Ungültige Anmeldedaten oder Konto deaktiviert.');
            Util::setOld(['userName' => $userName]);
            Util::redirect('index.php?view=login');
        }

        Util::redirect('index.php?view=dashboard');
    }

    private function logout(): never {
        $this->auth->logout();
        Util::redirect('index.php?view=login');
    }

    private function register(): never {
        $userName  = trim($_POST['userName'] ?? '');
        $firstName = trim($_POST['firstName'] ?? '');
        $lastName  = trim($_POST['lastName'] ?? '');
        $password  = $_POST['password'] ?? '';
        $confirm   = $_POST['passwordConfirm'] ?? '';

        $errors = [];
        if ($userName === '' || $firstName === '' || $lastName === '' || $password === '') {
            $errors[] = 'Bitte alle Pflichtfelder ausfüllen.';
        }
        if ($password !== $confirm) {
            $errors[] = 'Die Passwörter stimmen nicht überein.';
        }
        if (strlen($password) < 8) {
            $errors[] = 'Das Passwort muss mindestens 8 Zeichen lang sein.';
        }

        if (count($errors) > 0) {
            Util::setErrors($errors);
            Util::setOld(['userName' => $userName, 'firstName' => $firstName, 'lastName' => $lastName]);
            Util::redirect('index.php?view=register');
        }

        $userId = $this->auth->register($userName, $password, $firstName, $lastName);
        if ($userId === null) {
            Util::setError('Dieser Benutzername ist bereits vergeben.');
            Util::setOld(['userName' => $userName, 'firstName' => $firstName, 'lastName' => $lastName]);
            Util::redirect('index.php?view=register');
        }

        Util::setSuccess('Registrierung erfolgreich. Du kannst dich jetzt anmelden.');
        Util::redirect('index.php?view=login');
    }
}
