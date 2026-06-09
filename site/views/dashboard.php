<?php
declare(strict_types=1);

use VehicleTracker\CategoryColor;
use VehicleTracker\ConsumptionCalculator;
use VehicleTracker\Util;

$userId   = $currentUser->getId();
$vehicles = $vehicleDao->getByUserId($userId);
$years    = $expenseDao->getYearsByUser($userId);

$monthNames = [
    1 => 'Jänner', 2 => 'Februar', 3 => 'März',      4 => 'April',
    5 => 'Mai',    6 => 'Juni',    7 => 'Juli',      8 => 'August',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Dezember',
];
$money = static fn(float $v): string => number_format($v, 2, ',', '.') . ' €';

// Filters (same as the expenses list): vehicle / year / month, "all" = null each.
$vehicleIds = array_map(static fn($v) => $v->getId(), $vehicles);
$fVehicle   = (isset($_GET['fVehicle']) && ctype_digit((string)$_GET['fVehicle'])) ? (int)$_GET['fVehicle'] : null;
if ($fVehicle !== null && !in_array($fVehicle, $vehicleIds, true)) {
    $fVehicle = null;
}
$fYear  = (isset($_GET['fYear'])  && ctype_digit((string)$_GET['fYear']))  ? (int)$_GET['fYear']  : null;
$fMonth = (isset($_GET['fMonth']) && ctype_digit((string)$_GET['fMonth'])) ? (int)$_GET['fMonth'] : null;

// The month filter only applies within a specific year and offers only months
// that actually have bookings in that year. Without a year it is ignored.
$availableMonths = [];
if ($fYear !== null) {
    $availableMonths = $expenseDao->getActiveMonths($userId, $fYear, $fVehicle);
    if ($fMonth !== null && !in_array($fMonth, $availableMonths, true)) {
        $fMonth = null;
    }
} else {
    $fMonth = null;
}

// Label for the selected period, shown on the cost card.
if ($fYear !== null && $fMonth !== null) {
    $periodLabel = $monthNames[$fMonth] . ' ' . $fYear;
} elseif ($fYear !== null) {
    $periodLabel = (string)$fYear;
} else {
    $periodLabel = 'alle Zeiträume';
}

$periodTotal = $expenseDao->getTotalByUser($userId, $fYear, $fMonth, $fVehicle);

// Category breakdown: always show every category (0 € when nothing was booked in the period).
$allCategories = $categoryDao->getAll();
$catColors     = CategoryColor::map($allCategories);
$catTotals     = $expenseDao->getCategoryTotalsByUser($userId, $fYear, $fMonth, $fVehicle);
$catMap        = [];
foreach ($catTotals as $c) {
    $catMap[$c['name']] = $c['total'];
}
$bars = [];
foreach ($allCategories as $cat) {
    $bars[] = ['id' => $cat->getId(), 'name' => $cat->getName(), 'total' => $catMap[$cat->getName()] ?? 0.0];
}
$maxCat = 0.0;
foreach ($bars as $b) {
    $maxCat = max($maxCat, $b['total']);
}

// Filtered bookings: for the count and the latest five.
$filtered     = $expenseDao->getByUserIdFiltered($userId, $fVehicle, $fYear, $fMonth, null);
$bookingCount = count($filtered);
$recent       = array_slice($filtered, 0, 5);
$vehicleNames = [];
foreach ($vehicles as $v) {
    $vehicleNames[$v->getId()] = $v->getDisplayName();
}

// Scope label for the "latest bookings" section (follows the active filter).
$recentScope = ($fVehicle !== null ? ($vehicleNames[$fVehicle] ?? '—') : 'Alle Fahrzeuge') . ' · ' . $periodLabel;

// "Show all" carries every active filter into the expenses list.
$expensesQuery = ['view' => 'expenses'];
if ($fVehicle !== null) { $expensesQuery['fVehicle'] = $fVehicle; }
if ($fYear !== null)    { $expensesQuery['fYear']    = $fYear; }
if ($fMonth !== null)   { $expensesQuery['fMonth']   = $fMonth; }
$expensesLink = 'index.php?' . http_build_query($expensesQuery);

// A vehicle's metrics derived from its bookings (consumption, distance, km, cost).
$metricsOf = static function (array $fx): array {
    $consumption = ConsumptionCalculator::averagePer100Km($fx);
    $mileages    = [];
    $cost        = 0.0;
    foreach ($fx as $e) {
        if ($e->getMileage() !== null) { $mileages[] = $e->getMileage(); }
        $cost += $e->getCost();
    }
    return [
        'consumption' => $consumption,
        'latestKm'    => $mileages !== [] ? max($mileages) : null,
        'distance'    => count($mileages) >= 2 ? max($mileages) - min($mileages) : null,
        'cost'        => $cost,
    ];
};

// Mode: a single vehicle (filtered, or only one exists) OR all vehicles.
$singleVehicle = null;
if ($fVehicle !== null) {
    foreach ($vehicles as $v) {
        if ($v->getId() === $fVehicle) { $singleVehicle = $v; }
    }
} elseif (count($vehicles) === 1) {
    $singleVehicle = $vehicles[0];
}

$kpiLabel       = 'alle Fahrzeuge';
$kpiConsumption = null;
$kpiCostPerKm   = null;
$singleLatestKm = null;
$fleetCount     = count($vehicles);
$fleetDistance  = 0;

if ($singleVehicle !== null) {
    $m              = $metricsOf($expenseDao->getByVehicleId($singleVehicle->getId()));
    $kpiConsumption = $m['consumption'];
    $kpiCostPerKm   = ($m['distance'] !== null && $m['distance'] > 0) ? $m['cost'] / $m['distance'] : null;
    $kpiLabel       = $singleVehicle->getDisplayName();
    $singleLatestKm = $m['latestKm'];
} else {
    $fleetCost = 0.0;
    $consVals  = [];
    foreach ($vehicles as $v) {
        $m = $metricsOf($expenseDao->getByVehicleId($v->getId()));
        if ($m['distance'] !== null)    { $fleetDistance += $m['distance']; }
        if ($m['consumption'] !== null) { $consVals[] = $m['consumption']; }
        $fleetCost += $m['cost'];
    }
    $kpiConsumption = $consVals !== [] ? round(array_sum($consVals) / count($consVals), 1) : null;
    $kpiCostPerKm   = $fleetDistance > 0 ? $fleetCost / $fleetDistance : null;
}

require_once 'views/partials/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">Übersicht</h1>
        <p class="text-muted mb-0">Deine Fahrzeugkosten und Kennzahlen im Blick.</p>
    </div>
    <?php if (count($vehicles) > 0): ?>
        <form method="get" action="index.php" class="d-flex flex-wrap align-items-end gap-2">
            <input type="hidden" name="view" value="dashboard">
            <div>
                <label for="fVehicle" class="form-label mb-1 small">Fahrzeug</label>
                <select class="form-select form-select-sm" id="fVehicle" name="fVehicle" onchange="this.form.submit()">
                    <option value="">Alle Fahrzeuge</option>
                    <?php foreach ($vehicles as $v): ?>
                        <option value="<?php echo $v->getId(); ?>" <?php echo $fVehicle === $v->getId() ? 'selected' : ''; ?>>
                            <?php echo Util::escape($v->getDisplayName()); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="fYear" class="form-label mb-1 small">Jahr</label>
                <select class="form-select form-select-sm" id="fYear" name="fYear" onchange="this.form.submit()">
                    <option value="">Alle</option>
                    <?php foreach ($years as $y): ?>
                        <option value="<?php echo $y; ?>" <?php echo $fYear === $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="fMonth" class="form-label mb-1 small">Monat</label>
                <select class="form-select form-select-sm" id="fMonth" name="fMonth" onchange="this.form.submit()"
                        <?php echo $fYear === null ? 'disabled title="Erst ein Jahr wählen"' : ''; ?>>
                    <option value="">Alle</option>
                    <?php foreach ($availableMonths as $num): ?>
                        <option value="<?php echo $num; ?>" <?php echo $fMonth === $num ? 'selected' : ''; ?>><?php echo $monthNames[$num]; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php require_once 'views/partials/flash.php'; ?>

<?php if (count($vehicles) === 0): ?>
    <div class="alert alert-info">
        Noch keine Fahrzeuge. <a href="index.php?view=vehicle-form" class="alert-link">Lege dein erstes Fahrzeug an</a>,
        dann erscheinen hier deine Auswertungen.
    </div>
<?php else: ?>

    <div class="row g-3 mb-3">
        <!-- Total cost + category bars -->
        <div class="col-lg-8">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h2 class="h5 mb-0">Gesamtkosten</h2>
                            <span class="text-muted small">Konsolidierte Kosten im gewählten Zeitraum</span>
                        </div>
                        <div class="text-end">
                            <div class="display-6 fw-bold text-primary"><?php echo Util::escape($money($periodTotal)); ?></div>
                            <span class="small text-muted">
                                <span class="bi bi-calendar3" aria-hidden="true"></span> <?php echo Util::escape($periodLabel); ?>
                            </span>
                        </div>
                    </div>

                    <div class="bar-chart">
                        <?php foreach ($bars as $i => $c): ?>
                            <?php $h = ($maxCat > 0 && $c['total'] > 0) ? max(2, (int)round($c['total'] / $maxCat * 100)) : 0; ?>
                            <div class="bar-col" title="<?php echo Util::escape($c['name'] . ': ' . $money($c['total'])); ?>">
                                <div class="bar-amount small text-muted"><?php echo Util::escape($money($c['total'])); ?></div>
                                <div class="bar-track">
                                    <div class="bar" style="height: <?php echo $h; ?>%; background-color: <?php echo $catColors[$c['id']] ?? CategoryColor::FALLBACK; ?>;"></div>
                                </div>
                                <div class="bar-label small"><?php echo Util::escape($c['name']); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right card: single vehicle or all-vehicles overview -->
        <div class="col-lg-4">
            <?php if ($singleVehicle !== null): ?>
                <div class="card shadow-sm h-100 vehicle-highlight text-white">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start">
                            <span class="badge bg-white text-primary"><?php echo Util::escape($singleVehicle->getLicensePlate()); ?></span>
                            <span class="bi bi-car-front-fill fs-3 opacity-75" aria-hidden="true"></span>
                        </div>
                        <h2 class="h4 mt-3 mb-0"><?php echo Util::escape($singleVehicle->getDisplayName()); ?></h2>
                        <span class="opacity-75 small">
                            Erstzulassung <?php echo Util::escape($singleVehicle->getInitRegistration()->format('Y')); ?>
                        </span>
                        <div class="mt-auto d-flex justify-content-between align-items-end pt-4">
                            <div>
                                <div class="opacity-75 small">Letzter km-Stand</div>
                                <div class="h5 mb-0"><?php echo $singleLatestKm !== null ? Util::escape(number_format($singleLatestKm, 0, ',', '.')) . ' km' : '—'; ?></div>
                            </div>
                            <a href="index.php?view=vehicles" class="btn btn-sm btn-light">Fahrzeuge</a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card shadow-sm h-100 vehicle-highlight text-white">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start">
                            <span class="badge bg-white text-primary">Alle</span>
                            <span class="bi bi-car-front-fill fs-3 opacity-75" aria-hidden="true"></span>
                        </div>
                        <h2 class="h4 mt-3 mb-0"><?php echo $fleetCount; ?> Fahrzeuge</h2>
                        <span class="opacity-75 small">Alle Fahrzeuge im Überblick</span>
                        <div class="mt-auto d-flex justify-content-between align-items-end pt-4">
                            <div>
                                <div class="opacity-75 small">Gesamtstrecke</div>
                                <div class="h5 mb-0"><?php echo $fleetDistance > 0 ? Util::escape(number_format($fleetDistance, 0, ',', '.')) . ' km' : '—'; ?></div>
                            </div>
                            <a href="index.php?view=vehicles" class="btn btn-sm btn-light">Fahrzeuge</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Latest bookings -->
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <h2 class="h5 mb-0">Letzte Buchungen</h2>
                    <span class="text-muted small"><?php echo Util::escape($recentScope); ?></span>
                </div>
                <a href="<?php echo Util::escape($expensesLink); ?>" class="small">Alle anzeigen</a>
            </div>
            <?php if (count($recent) === 0): ?>
                <p class="text-muted mb-0">Noch keine Buchungen.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Datum</th>
                                <th>Fahrzeug</th>
                                <th>Kategorien</th>
                                <th>Notiz</th>
                                <th class="text-end">Betrag</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($recent as $expense): ?>
                            <tr>
                                <td class="text-nowrap"><?php echo Util::escape($expense->getDate()->format('d.m.Y')); ?></td>
                                <td><?php echo Util::escape($vehicleNames[$expense->getVehicleId()] ?? '—'); ?></td>
                                <td>
                                    <?php foreach ($categoryDao->getByExpenseId($expense->getId()) as $category): ?>
                                        <?php require 'views/partials/category-badge.php'; ?>
                                    <?php endforeach; ?>
                                </td>
                                <td class="text-muted"><?php echo Util::escape($expense->getNote() ?? ''); ?></td>
                                <td class="text-end text-nowrap fw-semibold"><?php echo Util::escape($money($expense->getCost())); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Key metrics -->
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="kpi-icon bi bi-speedometer2" aria-hidden="true"></span>
                    <div>
                        <div class="text-muted small">Ø Verbrauch · <?php echo Util::escape($kpiLabel); ?></div>
                        <div class="h4 mb-0"><?php echo $kpiConsumption !== null ? Util::escape(number_format($kpiConsumption, 1, ',', '.')) . ' l/100km' : '—'; ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="kpi-icon bi bi-cash-coin" aria-hidden="true"></span>
                    <div>
                        <div class="text-muted small">Kosten pro km · <?php echo Util::escape($kpiLabel); ?></div>
                        <div class="h4 mb-0"><?php echo $kpiCostPerKm !== null ? Util::escape(number_format($kpiCostPerKm, 2, ',', '.')) . ' €/km' : '—'; ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="kpi-icon bi bi-receipt" aria-hidden="true"></span>
                    <div>
                        <div class="text-muted small">Buchungen im Zeitraum</div>
                        <div class="h4 mb-0"><?php echo $bookingCount; ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php endif; ?>

<?php require_once 'views/partials/footer.php'; ?>
