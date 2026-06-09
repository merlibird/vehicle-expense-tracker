<?php
declare(strict_types=1);

use VehicleTracker\Controller\VehicleController;
use VehicleTracker\Util;

$id      = (int)($_GET['id'] ?? 0);
$vehicle = null;
if ($id > 0) {
    $vehicle = $vehicleDao->getById($id);
    if ($vehicle === null || !$vehicle->isActive() || $vehicle->getUserId() !== $currentUser->getId()) {
        Util::setError('Fahrzeug nicht gefunden.');
        Util::redirect('index.php?view=vehicles');
    }
}

$isEdit = $vehicle !== null;
$old    = Util::takeOld();

// Field values: from $old after a validation error, otherwise from the vehicle (edit) or empty (new).
$brand        = $old['brand']            ?? ($vehicle?->getBrand()        ?? '');
$model        = $old['model']            ?? ($vehicle?->getModel()        ?? '');
$licensePlate = $old['licensePlate']     ?? ($vehicle?->getLicensePlate() ?? '');
$initReg      = $old['initRegistration'] ?? ($vehicle?->getInitRegistration()->format('Y-m-d') ?? '');

require_once 'views/partials/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="d-flex align-items-center gap-2 mb-4">
            <a href="index.php?view=vehicles" class="btn btn-sm btn-outline-secondary" aria-label="Zurück zur Fahrzeugliste">
                <span class="bi bi-arrow-left" aria-hidden="true"></span>
            </a>
            <h1 class="h3 mb-0"><?php echo $isEdit ? 'Fahrzeug bearbeiten' : 'Fahrzeug hinzufügen'; ?></h1>
        </div>

        <?php require_once 'views/partials/flash.php'; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <form method="post" action="index.php">
                    <input type="hidden" name="<?php echo VehicleController::ACTION; ?>" value="<?php echo VehicleController::ACTION_SAVE; ?>">
                    <?php if ($isEdit): ?>
                        <input type="hidden" name="id" value="<?php echo $vehicle->getId(); ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="brand" class="form-label">Marke</label>
                        <input type="text" class="form-control" id="brand" name="brand" maxlength="100"
                               value="<?php echo Util::escape($brand); ?>" autofocus required>
                    </div>

                    <div class="mb-3">
                        <label for="model" class="form-label">Modell</label>
                        <input type="text" class="form-control" id="model" name="model" maxlength="100"
                               value="<?php echo Util::escape($model); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="licensePlate" class="form-label">Kennzeichen</label>
                        <input type="text" class="form-control" id="licensePlate" name="licensePlate" maxlength="20"
                               value="<?php echo Util::escape($licensePlate); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="initRegistration" class="form-label">Erstzulassung</label>
                        <input type="date" class="form-control" id="initRegistration" name="initRegistration"
                               value="<?php echo Util::escape($initReg); ?>" required>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><?php echo $isEdit ? 'Speichern' : 'Anlegen'; ?></button>
                        <a href="index.php?view=vehicles" class="btn btn-outline-secondary">Abbrechen</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'views/partials/footer.php'; ?>
