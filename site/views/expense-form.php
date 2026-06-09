<?php
declare(strict_types=1);

use VehicleTracker\Controller\ExpenseController;
use VehicleTracker\FuelExpense;
use VehicleTracker\Util;

// Edit mode: load the booking and check ownership (via its vehicle) before any output.
$id      = (int)($_GET['id'] ?? 0);
$expense = null;
if ($id > 0) {
    $expense          = $expenseDao->getById($id);
    $vehicleOfExpense = $expense !== null ? $vehicleDao->getById($expense->getVehicleId()) : null;
    if ($expense === null || !$expense->isActive()
        || $vehicleOfExpense === null || $vehicleOfExpense->getUserId() !== $currentUser->getId()) {
        Util::setError('Buchung nicht gefunden.');
        Util::redirect('index.php?view=expenses');
    }
}

// No bookings without a vehicle.
$vehicles = $vehicleDao->getByUserId($currentUser->getId());
if (count($vehicles) === 0) {
    Util::setError('Du brauchst zuerst ein Fahrzeug, um Buchungen zu erfassen.');
    Util::redirect('index.php?view=vehicles');
}

$categories = $categoryDao->getAll();

// The 'Tanken' category is special: selecting it turns the booking into a fuel booking.
$fuelCategoryId = null;
foreach ($categories as $c) {
    if ($c->getName() === ExpenseController::FUEL_CATEGORY) {
        $fuelCategoryId = $c->getId();
        break;
    }
}

$isEdit = $expense !== null;
$old    = Util::takeOld();

// Selected categories: from $old after an error, otherwise from the DB (edit), otherwise none.
if (isset($old['categories']) && is_array($old['categories'])) {
    $selectedCategoryIds = array_map('intval', $old['categories']);
} elseif ($isEdit) {
    $selectedCategoryIds = array_map(static fn($c) => $c->getId(), $categoryDao->getByExpenseId($expense->getId()));
} else {
    $selectedCategoryIds = [];
}

// Fuel booking = 'Tanken' selected. When editing, fixed by the existing type.
$isFuel = $isEdit
    ? ($expense instanceof FuelExpense)
    : ($fuelCategoryId !== null && in_array($fuelCategoryId, $selectedCategoryIds, true));

// Field values: $old > booking (edit) > empty.
$vehicleVal = $old['vehicleId']     ?? ($isEdit ? (string)$expense->getVehicleId() : '');
$dateVal    = $old['date']          ?? ($isEdit ? $expense->getDate()->format('Y-m-d') : '');
$costVal    = $old['cost']          ?? ($isEdit ? number_format($expense->getCost(), 2, '.', '') : '');
$mileageVal = $old['mileage']       ?? ($isEdit && $expense->getMileage() !== null ? (string)$expense->getMileage() : '');
$noteVal    = $old['note']          ?? ($isEdit ? ($expense->getNote() ?? '') : '');
$litersVal  = $old['liters']        ?? ($isFuel && $isEdit ? number_format($expense->getLiters(), 2, '.', '') : '');
$priceVal   = $old['pricePerLiter'] ?? ($isFuel && $isEdit ? number_format($expense->getPricePerLiter(), 3, '.', '') : '');

require_once 'views/partials/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-9 col-lg-7">
        <div class="d-flex align-items-center gap-2 mb-4">
            <a href="index.php?view=expenses" class="btn btn-sm btn-outline-secondary" aria-label="Zurück zur Ausgabenliste">
                <span class="bi bi-arrow-left" aria-hidden="true"></span>
            </a>
            <h1 class="h3 mb-0"><?php echo $isEdit ? 'Buchung bearbeiten' : 'Buchung hinzufügen'; ?></h1>
        </div>

        <?php require_once 'views/partials/flash.php'; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <form method="post" action="index.php">
                    <input type="hidden" name="<?php echo ExpenseController::ACTION; ?>" value="<?php echo ExpenseController::ACTION_SAVE; ?>">
                    <?php if ($isEdit): ?>
                        <input type="hidden" name="id" value="<?php echo $expense->getId(); ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="vehicleId" class="form-label">Fahrzeug</label>
                        <select class="form-select" id="vehicleId" name="vehicleId" required>
                            <option value="">– bitte wählen –</option>
                            <?php foreach ($vehicles as $v): ?>
                                <option value="<?php echo $v->getId(); ?>" <?php echo (string)$v->getId() === (string)$vehicleVal ? 'selected' : ''; ?>>
                                    <?php echo Util::escape($v->getDisplayName() . ' (' . $v->getLicensePlate() . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-sm-6 mb-3">
                            <label for="date" class="form-label">Datum</label>
                            <input type="date" class="form-control" id="date" name="date"
                                   value="<?php echo Util::escape($dateVal); ?>" required>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <label for="cost" class="form-label">Betrag (€)</label>
                            <input type="text" inputmode="decimal" class="form-control" id="cost" name="cost"
                                   value="<?php echo Util::escape($costVal); ?>" placeholder="z. B. 78,45" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="mileage" class="form-label">Kilometerstand <span class="text-muted">(optional)</span></label>
                        <input type="number" min="0" step="1" class="form-control" id="mileage" name="mileage"
                               value="<?php echo Util::escape($mileageVal); ?>">
                    </div>

                    <div class="mb-3">
                        <span class="form-label d-block">Kategorien</span>
                        <?php foreach ($categories as $category): ?>
                            <?php
                            $catId    = $category->getId();
                            $isTanken = $catId === $fuelCategoryId;
                            ?>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="categories[]"
                                       id="cat<?php echo $catId; ?>" value="<?php echo $catId; ?>"
                                       <?php echo in_array($catId, $selectedCategoryIds, true) ? 'checked' : ''; ?>
                                       <?php echo ($isEdit && $isTanken) ? 'disabled' : ''; ?>>
                                <label class="form-check-label" for="cat<?php echo $catId; ?>">
                                    <?php echo Util::escape($category->getName()); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                        <?php if ($isEdit): ?>
                            <div class="form-text">„Tanken" (Tankbuchung) kann nach dem Anlegen nicht mehr geändert werden.</div>
                        <?php else: ?>
                            <div class="form-text">Mindestens eine Kategorie wählen. „Tanken" macht daraus eine Tankbuchung (mit Liter &amp; Preis pro Liter).</div>
                        <?php endif; ?>
                    </div>

                    <div id="fuelFields" class="row" <?php echo $isFuel ? '' : 'style="display:none"'; ?>>
                        <div class="col-sm-6 mb-3">
                            <label for="liters" class="form-label">Liter</label>
                            <input type="text" inputmode="decimal" class="form-control" id="liters" name="liters"
                                   value="<?php echo Util::escape($litersVal); ?>" placeholder="z. B. 46,15">
                        </div>
                        <div class="col-sm-6 mb-3">
                            <label for="pricePerLiter" class="form-label">Preis pro Liter (€)</label>
                            <input type="text" inputmode="decimal" class="form-control" id="pricePerLiter" name="pricePerLiter"
                                   value="<?php echo Util::escape($priceVal); ?>" placeholder="z. B. 1,699">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="note" class="form-label">Notiz <span class="text-muted">(optional)</span></label>
                        <textarea class="form-control" id="note" name="note" rows="2" maxlength="500"><?php echo Util::escape($noteVal); ?></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><?php echo $isEdit ? 'Speichern' : 'Anlegen'; ?></button>
                        <a href="index.php?view=expenses" class="btn btn-outline-secondary">Abbrechen</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php if (!$isEdit && $fuelCategoryId !== null): ?>
<script>
    document.getElementById('cat<?php echo $fuelCategoryId; ?>').addEventListener('change', function () {
        document.getElementById('fuelFields').style.display = this.checked ? '' : 'none';
    });
</script>
<?php endif; ?>

<?php require_once 'views/partials/footer.php'; ?>
