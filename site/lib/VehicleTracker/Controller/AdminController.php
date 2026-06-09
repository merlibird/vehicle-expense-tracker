<?php
declare(strict_types=1);

namespace VehicleTracker\Controller;

use Data\Dao\IUserDao;
use Data\Dao\ILogDao;
use VehicleTracker\AuthenticationManager;
use VehicleTracker\Util;

class AdminController extends AbstractController {

    public const string ACTION_SET_ACTIVE = 'user-set-active';

    public function __construct(
        AuthenticationManager $auth,
        ILogDao $logDao,
        private readonly IUserDao $userDao,
    ) {
        parent::__construct($auth, $logDao);
    }

    public function handles(string $action): bool {
        return $action === self::ACTION_SET_ACTIVE;
    }

    public function dispatch(string $action): void {
        match ($action) {
            self::ACTION_SET_ACTIVE => $this->setActive(),
        };
    }

    private function setActive(): never {
        $admin    = $this->auth->requireAdmin();
        $targetId = (int)($_POST['id'] ?? 0);
        $active   = ($_POST['active'] ?? '') === '1';

        // An admin cannot change their own status (prevents locking yourself out).
        if ($targetId === $admin->getId()) {
            Util::setError('Du kannst deinen eigenen Status nicht ändern.');
            Util::redirect('index.php?view=admin');
        }

        $target = $this->userDao->getById($targetId);
        if ($target === null) {
            Util::setError('Benutzer nicht gefunden.');
            Util::redirect('index.php?view=admin');
        }

        $this->userDao->setActive($targetId, $active);

        $this->logAction(
            $admin,
            ($active ? 'ADMIN_ACTIVATE_USER' : 'ADMIN_DEACTIVATE_USER') . ':' . $targetId
        );

        Util::setSuccess($active ? 'Benutzer aktiviert.' : 'Benutzer deaktiviert.');
        Util::redirect('index.php?view=admin');
    }
}
