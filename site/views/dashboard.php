<?php
declare(strict_types=1);

use VehicleTracker\DashboardData;
use VehicleTracker\Util;

// Read the filter from GET (only valid integers, otherwise ignored); the view
// model validates them against the user's data and computes everything else.
$filterId = static fn(string $key): ?int =>
    (isset($_GET[$key]) && ctype_digit((string)$_GET[$key])) ? (int)$_GET[$key] : null;

$data = DashboardData::build(
    $vehicleDao,
    $expenseDao,
    $categoryDao,
    $currentUser->getId(),
    $filterId('fVehicle'),
    $filterId('fYear'),
    $filterId('fMonth'),
);

$money = static fn(float $v): string => number_format($v, 2, ',', '.') . ' €';

require_once 'views/partials/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">Übersicht</h1>
        <p class="text-muted mb-0">Deine Fahrzeugkosten und Kennzahlen im Blick.</p>
    </div>
    <?php if (count($data->vehicles) > 0): ?>
        <form method="get" action="index.php" class="d-flex flex-wrap align-items-end gap-2">
            <input type="hidden" name="view" value="dashboard">
            <div>
                <label for="fVehicle" class="form-label mb-1 small">Fahrzeug</label>
                <select class="form-select form-select-sm" id="fVehicle" name="fVehicle" onchange="this.form.submit()">
                    <option value="">Alle Fahrzeuge</option>
                    <?php foreach ($data->vehicles as $v): ?>
                        <option value="<?php echo $v->getId(); ?>" <?php echo $data->vehicleId === $v->getId() ? 'selected' : ''; ?>>
                            <?php echo Util::escape($v->getDisplayName()); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="fYear" class="form-label mb-1 small">Jahr</label>
                <select class="form-select form-select-sm" id="fYear" name="fYear" onchange="this.form.submit()">
                    <option value="">Alle</option>
                    <?php foreach ($data->years as $y): ?>
                        <option value="<?php echo $y; ?>" <?php echo $data->year === $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="fMonth" class="form-label mb-1 small">Monat</label>
                <select class="form-select form-select-sm" id="fMonth" name="fMonth" onchange="this.form.submit()"
                        <?php echo $data->year === null ? 'disabled title="Erst ein Jahr wählen"' : ''; ?>>
                    <option value="">Alle</option>
                    <?php foreach ($data->availableMonths as $num => $name): ?>
                        <option value="<?php echo $num; ?>" <?php echo $data->month === $num ? 'selected' : ''; ?>><?php echo $name; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php require_once 'views/partials/flash.php'; ?>

<?php if (count($data->vehicles) === 0): ?>
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
                            <div class="display-6 fw-bold text-primary"><?php echo Util::escape($money($data->periodTotal)); ?></div>
                            <span class="small text-muted">
                                <span class="bi bi-calendar3" aria-hidden="true"></span> <?php echo Util::escape($data->periodLabel); ?>
                            </span>
                        </div>
                    </div>

                    <div class="bar-chart">
                        <?php foreach ($data->bars as $c): ?>
                            <?php $h = ($data->maxCat > 0 && $c['total'] > 0) ? max(2, (int)round($c['total'] / $data->maxCat * 100)) : 0; ?>
                            <div class="bar-col" title="<?php echo Util::escape($c['name'] . ': ' . $money($c['total'])); ?>">
                                <div class="bar-amount small text-muted"><?php echo Util::escape($money($c['total'])); ?></div>
                                <div class="bar-track">
                                    <div class="bar" style="height: <?php echo $h; ?>%; background-color: <?php echo $c['color']; ?>;"></div>
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
            <?php if ($data->singleVehicle !== null): ?>
                <div class="card shadow-sm h-100 vehicle-highlight text-white">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start">
                            <span class="badge bg-white text-primary"><?php echo Util::escape($data->singleVehicle->getLicensePlate()); ?></span>
                            <span class="bi bi-car-front-fill fs-3 opacity-75" aria-hidden="true"></span>
                        </div>
                        <h2 class="h4 mt-3 mb-0"><?php echo Util::escape($data->singleVehicle->getDisplayName()); ?></h2>
                        <span class="opacity-75 small">
                            Erstzulassung <?php echo Util::escape($data->singleVehicle->getInitRegistration()->format('Y')); ?>
                        </span>
                        <div class="mt-auto d-flex justify-content-between align-items-end pt-4">
                            <div>
                                <div class="opacity-75 small">Letzter km-Stand</div>
                                <div class="h5 mb-0"><?php echo $data->singleLatestKm !== null ? Util::escape(number_format($data->singleLatestKm, 0, ',', '.')) . ' km' : '—'; ?></div>
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
                        <h2 class="h4 mt-3 mb-0"><?php echo $data->fleetCount; ?> Fahrzeuge</h2>
                        <span class="opacity-75 small">Alle Fahrzeuge im Überblick</span>
                        <div class="mt-auto d-flex justify-content-between align-items-end pt-4">
                            <div>
                                <div class="opacity-75 small">Gesamtstrecke</div>
                                <div class="h5 mb-0"><?php echo $data->fleetDistance > 0 ? Util::escape(number_format($data->fleetDistance, 0, ',', '.')) . ' km' : '—'; ?></div>
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
                    <span class="text-muted small"><?php echo Util::escape($data->recentScope); ?></span>
                </div>
                <a href="<?php echo Util::escape($data->expensesLink); ?>" class="small">Alle anzeigen</a>
            </div>
            <?php if (count($data->recent) === 0): ?>
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
                        <?php foreach ($data->recent as $expense): ?>
                            <tr>
                                <td class="text-nowrap"><?php echo Util::escape($expense->getDate()->format('d.m.Y')); ?></td>
                                <td><?php echo Util::escape($data->vehicleNames[$expense->getVehicleId()] ?? '—'); ?></td>
                                <td>
                                    <?php foreach ($data->recentCategories[$expense->getId()] ?? [] as $category): ?>
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
                        <div class="text-muted small">Ø Verbrauch · <?php echo Util::escape($data->kpiLabel); ?></div>
                        <div class="h4 mb-0"><?php echo $data->kpiConsumption !== null ? Util::escape(number_format($data->kpiConsumption, 1, ',', '.')) . ' l/100km' : '—'; ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="kpi-icon bi bi-cash-coin" aria-hidden="true"></span>
                    <div>
                        <div class="text-muted small">Kosten pro km · <?php echo Util::escape($data->kpiLabel); ?></div>
                        <div class="h4 mb-0"><?php echo $data->kpiCostPerKm !== null ? Util::escape(number_format($data->kpiCostPerKm, 2, ',', '.')) . ' €/km' : '—'; ?></div>
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
                        <div class="h4 mb-0"><?php echo $data->bookingCount; ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php endif; ?>

<?php require_once 'views/partials/footer.php'; ?>
