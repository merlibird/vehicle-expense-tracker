<?php
declare(strict_types=1);

use VehicleTracker\CategoryColor;
use VehicleTracker\Controller\ExpenseController;
use VehicleTracker\FuelExpense;
use VehicleTracker\PagingResult;
use VehicleTracker\Util;

$userId     = $currentUser->getId();
$vehicles   = $vehicleDao->getByUserId($userId);
$categories = $categoryDao->getAll();
$catColors  = CategoryColor::map($categories);
$years      = $expenseDao->getYearsByUser($userId);

// Filters from GET (only valid integers, otherwise ignored).
$fVehicle  = (isset($_GET['fVehicle'])  && ctype_digit((string)$_GET['fVehicle']))  ? (int)$_GET['fVehicle']  : null;
$fYear     = (isset($_GET['fYear'])     && ctype_digit((string)$_GET['fYear']))     ? (int)$_GET['fYear']     : null;
$fMonth    = (isset($_GET['fMonth'])    && ctype_digit((string)$_GET['fMonth']))    ? (int)$_GET['fMonth']    : null;
$fCategory = (isset($_GET['fCategory']) && ctype_digit((string)$_GET['fCategory'])) ? (int)$_GET['fCategory'] : null;

// The month filter only applies within a year and offers only months that have bookings.
$availableMonths = [];
if ($fYear !== null) {
    $availableMonths = $expenseDao->getActiveMonths($userId, $fYear, $fVehicle);
    if ($fMonth !== null && !in_array($fMonth, $availableMonths, true)) {
        $fMonth = null;
    }
} else {
    $fMonth = null;
}

$hasFilter = $fVehicle !== null || $fYear !== null || $fMonth !== null || $fCategory !== null;

// Pagination: get the total, clamp the page to a valid range, then load only that page.
$perPage   = 10;
$total     = $expenseDao->countByUserIdFiltered($userId, $fVehicle, $fYear, $fMonth, $fCategory);
$pageCount = max(1, (int)ceil($total / $perPage));
$page      = (isset($_GET['page']) && ctype_digit((string)$_GET['page'])) ? (int)$_GET['page'] : 1;
$page      = max(1, min($page, $pageCount));
$offset    = ($page - 1) * $perPage;
$expenses  = $expenseDao->getByUserIdFilteredPaged($userId, $fVehicle, $fYear, $fMonth, $fCategory, $perPage, $offset);
$paging    = new PagingResult($expenses, $offset, $perPage, $total);

// Carry the active filters into the page links.
$pagingBaseParams = ['view' => 'expenses'];
if ($fVehicle !== null)  { $pagingBaseParams['fVehicle']  = $fVehicle; }
if ($fYear !== null)     { $pagingBaseParams['fYear']     = $fYear; }
if ($fMonth !== null)    { $pagingBaseParams['fMonth']    = $fMonth; }
if ($fCategory !== null) { $pagingBaseParams['fCategory'] = $fCategory; }

// Vehicle display names, keyed by id.
$vehicleNames = [];
foreach ($vehicles as $v) {
    $vehicleNames[$v->getId()] = $v->getDisplayName();
}
$hasVehicles = count($vehicles) > 0;

$monthNames = [
    1 => 'Jänner', 2 => 'Februar', 3 => 'März',      4 => 'April',
    5 => 'Mai',    6 => 'Juni',    7 => 'Juli',      8 => 'August',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Dezember',
];

require_once 'views/partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Meine Ausgaben</h1>
    <?php if ($hasVehicles): ?>
        <a href="index.php?view=expense-form" class="btn btn-primary">
            <span class="bi bi-plus-lg" aria-hidden="true"></span> Buchung hinzufügen
        </a>
    <?php endif; ?>
</div>

<?php require_once 'views/partials/flash.php'; ?>

<?php if (!$hasVehicles): ?>
    <div class="alert alert-info">
        Du brauchst zuerst ein Fahrzeug, um Buchungen zu erfassen.
        <a href="index.php?view=vehicle-form" class="alert-link">Jetzt ein Fahrzeug anlegen</a>.
    </div>
<?php else: ?>
    <form method="get" action="index.php" class="row g-2 align-items-end mb-3">
        <input type="hidden" name="view" value="expenses">
        <div class="col-6 col-md-3">
            <label for="fVehicle" class="form-label mb-1 small">Fahrzeug</label>
            <select class="form-select form-select-sm" id="fVehicle" name="fVehicle" onchange="this.form.submit()">
                <option value="">Alle</option>
                <?php foreach ($vehicles as $v): ?>
                    <option value="<?php echo $v->getId(); ?>" <?php echo $fVehicle === $v->getId() ? 'selected' : ''; ?>>
                        <?php echo Util::escape($v->getDisplayName()); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label for="fYear" class="form-label mb-1 small">Jahr</label>
            <select class="form-select form-select-sm" id="fYear" name="fYear" onchange="this.form.submit()">
                <option value="">Alle</option>
                <?php foreach ($years as $y): ?>
                    <option value="<?php echo $y; ?>" <?php echo $fYear === $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label for="fMonth" class="form-label mb-1 small">Monat</label>
            <select class="form-select form-select-sm" id="fMonth" name="fMonth" onchange="this.form.submit()"
                    <?php echo $fYear === null ? 'disabled title="Erst ein Jahr wählen"' : ''; ?>>
                <option value="">Alle</option>
                <?php foreach ($availableMonths as $num): ?>
                    <option value="<?php echo $num; ?>" <?php echo $fMonth === $num ? 'selected' : ''; ?>><?php echo $monthNames[$num]; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-md-3">
            <label for="fCategory" class="form-label mb-1 small">Kategorie</label>
            <select class="form-select form-select-sm" id="fCategory" name="fCategory" onchange="this.form.submit()">
                <option value="">Alle</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category->getId(); ?>" <?php echo $fCategory === $category->getId() ? 'selected' : ''; ?>>
                        <?php echo Util::escape($category->getName()); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php if ($hasFilter): ?>
            <div class="col-12 col-md-2 d-flex align-items-end">
                <a href="index.php?view=expenses" class="btn btn-sm btn-outline-secondary">Zurücksetzen</a>
            </div>
        <?php endif; ?>
    </form>

    <?php if ($total === 0): ?>
        <div class="alert alert-info">
            <?php echo $hasFilter ? 'Keine Buchungen für die gewählten Filter.' : 'Du hast noch keine Buchungen erfasst.'; ?>
        </div>
    <?php else: ?>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Datum</th>
                    <th>Fahrzeug</th>
                    <th>Kategorien</th>
                    <th class="text-end">Betrag</th>
                    <th class="text-end">km-Stand</th>
                    <th>Details</th>
                    <th class="text-end">Aktionen</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($expenses as $expense): ?>
                <?php
                $expenseCategories = $categoryDao->getByExpenseId($expense->getId());
                $isFuel            = $expense instanceof FuelExpense;
                ?>
                <tr>
                    <td class="text-nowrap"><?php echo Util::escape($expense->getDate()->format('d.m.Y')); ?></td>
                    <td><?php echo Util::escape($vehicleNames[$expense->getVehicleId()] ?? '—'); ?></td>
                    <td>
                        <?php foreach ($expenseCategories as $category): ?>
                            <?php require 'views/partials/category-badge.php'; ?>
                        <?php endforeach; ?>
                    </td>
                    <td class="text-end text-nowrap"><?php echo Util::escape(number_format($expense->getCost(), 2, ',', '.')); ?>&nbsp;€</td>
                    <td class="text-end text-nowrap"><?php echo $expense->getMileage() !== null ? Util::escape(number_format($expense->getMileage(), 0, ',', '.')) : '—'; ?></td>
                    <td class="small text-muted">
                        <?php if ($isFuel): ?>
                            <?php echo Util::escape(number_format($expense->getLiters(), 2, ',', '.')); ?>&nbsp;l
                            · <?php echo Util::escape(number_format($expense->getPricePerLiter(), 3, ',', '.')); ?>&nbsp;€/l<br>
                        <?php endif; ?>
                        <?php echo Util::escape($expense->getNote() ?? ''); ?>
                    </td>
                    <td class="text-end text-nowrap">
                        <a href="index.php?view=expense-form&id=<?php echo $expense->getId(); ?>"
                           class="btn btn-sm btn-outline-secondary">Bearbeiten</a>
                        <form method="post" action="index.php" class="d-inline"
                              onsubmit="return confirm('Diese Buchung wirklich löschen?');">
                            <input type="hidden" name="<?php echo ExpenseController::ACTION; ?>" value="<?php echo ExpenseController::ACTION_DELETE; ?>">
                            <input type="hidden" name="id" value="<?php echo $expense->getId(); ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">Löschen</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php require 'views/partials/pagination.php'; ?>
    <?php endif; ?>
<?php endif; ?>

<?php require_once 'views/partials/footer.php'; ?>
