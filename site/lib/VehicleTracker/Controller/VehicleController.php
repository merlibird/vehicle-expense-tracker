<?php
declare(strict_types=1);

namespace VehicleTracker\Controller;

use Data\Dao\IVehicleDao;
use Data\Dao\ILogDao;
use VehicleTracker\AuthenticationManager;
use VehicleTracker\Util;

class VehicleController extends AbstractController {

    use VehicleOwnership;

    public const string ACTION_SAVE   = 'vehicle-save';
    public const string ACTION_DELETE = 'vehicle-delete';

    public function __construct(
        AuthenticationManager $auth,
        ILogDao $logDao,
        private readonly IVehicleDao $vehicleDao,
    ) {
        parent::__construct($auth, $logDao);
    }

    public function handles(string $action): bool {
        return in_array($action, [self::ACTION_SAVE, self::ACTION_DELETE], true);
    }

    public function dispatch(string $action): void {
        match ($action) {
            self::ACTION_SAVE   => $this->saveVehicle(),
            self::ACTION_DELETE => $this->deleteVehicle(),
        };
    }

    private function saveVehicle(): never {
        $user = $this->auth->requireUser();

        $id           = (int)($_POST['id'] ?? 0);
        $brand        = trim($_POST['brand'] ?? '');
        $model        = trim($_POST['model'] ?? '');
        $licensePlate = trim($_POST['licensePlate'] ?? '');
        $regInput     = trim($_POST['initRegistration'] ?? '');

        // When editing, the vehicle must exist and belong to the user.
        if ($id > 0 && !$this->ownsVehicle($id, $user->getId())) {
            Util::setError('Fahrzeug nicht gefunden.');
            Util::redirect('index.php?view=vehicles');
        }

        $errors = [];
        if ($brand === '' || $model === '' || $licensePlate === '' || $regInput === '') {
            $errors[] = 'Bitte alle Felder ausfüllen.';
        }
        if (mb_strlen($brand) > 100 || mb_strlen($model) > 100) {
            $errors[] = 'Marke und Modell dürfen höchstens 100 Zeichen lang sein.';
        }
        if (mb_strlen($licensePlate) > 20) {
            $errors[] = 'Das Kennzeichen darf höchstens 20 Zeichen lang sein.';
        }

        $initRegistration = Util::parseDate($regInput);
        if ($regInput !== '' && $initRegistration === null) {
            $errors[] = 'Bitte ein gültiges Erstzulassungsdatum angeben.';
        } elseif ($initRegistration !== null && $initRegistration > new \DateTimeImmutable('today')) {
            $errors[] = 'Die Erstzulassung darf nicht in der Zukunft liegen.';
        }

        if (count($errors) > 0) {
            Util::setErrors($errors);
            Util::setOld([
                'brand'            => $brand,
                'model'            => $model,
                'licensePlate'     => $licensePlate,
                'initRegistration' => $regInput,
            ]);
            $target = $id > 0 ? 'index.php?view=vehicle-form&id=' . $id : 'index.php?view=vehicle-form';
            Util::redirect($target);
        }

        if ($id > 0) {
            $this->vehicleDao->update($id, $brand, $model, $licensePlate, $initRegistration);
            $this->logAction($user, 'VEHICLE_UPDATE:' . $id);
            Util::setSuccess('Fahrzeug aktualisiert.');
        } else {
            $newId = $this->vehicleDao->create($user->getId(), $brand, $model, $licensePlate, $initRegistration);
            $this->logAction($user, 'VEHICLE_CREATE:' . $newId);
            Util::setSuccess('Fahrzeug hinzugefügt.');
        }
        Util::redirect('index.php?view=vehicles');
    }

    private function deleteVehicle(): never {
        $user = $this->auth->requireUser();
        $id   = (int)($_POST['id'] ?? 0);

        if ($id <= 0 || !$this->ownsVehicle($id, $user->getId())) {
            Util::setError('Fahrzeug nicht gefunden.');
            Util::redirect('index.php?view=vehicles');
        }

        $this->vehicleDao->softDelete($id);
        $this->logAction($user, 'VEHICLE_DELETE:' . $id);
        Util::setSuccess('Fahrzeug gelöscht.');
        Util::redirect('index.php?view=vehicles');
    }
}
