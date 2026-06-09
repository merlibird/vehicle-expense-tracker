<?php
declare(strict_types=1);

namespace VehicleTracker\Controller;

use Data\Dao\IUserDao;
use Data\Dao\ILogDao;
use VehicleTracker\AuthenticationManager;
use VehicleTracker\Util;

class ProfileController extends AbstractController {

    public const string ACTION_UPDATE   = 'profile-update';
    public const string ACTION_PASSWORD = 'profile-password';

    public function __construct(
        AuthenticationManager $auth,
        ILogDao $logDao,
        private readonly IUserDao $userDao,
    ) {
        parent::__construct($auth, $logDao);
    }

    public function handles(string $action): bool {
        return in_array($action, [self::ACTION_UPDATE, self::ACTION_PASSWORD], true);
    }

    public function dispatch(string $action): void {
        match ($action) {
            self::ACTION_UPDATE   => $this->updateProfile(),
            self::ACTION_PASSWORD => $this->changePassword(),
        };
    }

    private function updateProfile(): never {
        $user      = $this->auth->requireUser();
        $userName  = trim($_POST['userName'] ?? '');
        $firstName = trim($_POST['firstName'] ?? '');
        $lastName  = trim($_POST['lastName'] ?? '');

        $errors = [];
        if ($userName === '' || $firstName === '' || $lastName === '') {
            $errors[] = 'Bitte alle Felder ausfüllen.';
        }
        if (mb_strlen($userName) > 50) {
            $errors[] = 'Der Benutzername darf höchstens 50 Zeichen lang sein.';
        }
        if (mb_strlen($firstName) > 100 || mb_strlen($lastName) > 100) {
            $errors[] = 'Vor- und Nachname dürfen höchstens 100 Zeichen lang sein.';
        }

        // The username must be unique (unless it already belongs to this user).
        $existing = $this->userDao->getByUserName($userName);
        if ($existing !== null && $existing->getId() !== $user->getId()) {
            $errors[] = 'Dieser Benutzername ist bereits vergeben.';
        }

        if (count($errors) > 0) {
            Util::setErrors($errors);
            Util::setOld(['userName' => $userName, 'firstName' => $firstName, 'lastName' => $lastName]);
            Util::redirect('index.php?view=profile');
        }

        $this->userDao->update($user->getId(), $userName, $firstName, $lastName);
        $this->logAction($user, 'PROFILE_UPDATE');
        Util::setSuccess('Profil aktualisiert.');
        Util::redirect('index.php?view=profile');
    }

    private function changePassword(): never {
        $user    = $this->auth->requireUser();
        $current = $_POST['currentPassword'] ?? '';
        $new     = $_POST['newPassword'] ?? '';
        $confirm = $_POST['newPasswordConfirm'] ?? '';

        $errors = [];
        if ($current === '' || $new === '') {
            $errors[] = 'Bitte aktuelles und neues Passwort eingeben.';
        }
        if ($new !== $confirm) {
            $errors[] = 'Die neuen Passwörter stimmen nicht überein.';
        }
        if (strlen($new) < 8) {
            $errors[] = 'Das neue Passwort muss mindestens 8 Zeichen lang sein.';
        }

        if (count($errors) > 0) {
            Util::setErrors($errors);
            Util::redirect('index.php?view=profile');
        }

        if (!$this->auth->changePassword($user->getId(), $current, $new)) {
            Util::setError('Das aktuelle Passwort ist nicht korrekt.');
            Util::redirect('index.php?view=profile');
        }

        Util::setSuccess('Passwort geändert.');
        Util::redirect('index.php?view=profile');
    }
}
