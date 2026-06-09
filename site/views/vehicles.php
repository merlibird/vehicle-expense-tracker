<?php
declare(strict_types=1);

use VehicleTracker\Controller\VehicleController;
use VehicleTracker\PagingResult;
use VehicleTracker\Util;

$userId = $currentUser->getId();

$perPage   = 10;
$total     = $vehicleDao->countByUserId($userId);
$pageCount = max(1, (int)ceil($total / $perPage));
$page      = (isset($_GET['page']) && ctype_digit((string)$_GET['page'])) ? (int)$_GET['page'] : 1;
$page      = max(1, min($page, $pageCount));
$offset    = ($page - 1) * $perPage;
$vehicles  = $vehicleDao->getByUserIdPaged($userId, $perPage, $offset);
$paging    = new PagingResult($vehicles, $offset, $perPage, $total);
$pagingBaseParams = ['view' => 'vehicles'];

require_once 'views/partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Meine Fahrzeuge</h1>
    <a href="index.php?view=vehicle-form" class="btn btn-primary">
        <span class="bi bi-plus-lg" aria-hidden="true"></span> Fahrzeug hinzufügen
    </a>
</div>

<?php require_once 'views/partials/flash.php'; ?>

<?php if ($total === 0): ?>
    <div class="alert alert-info">Du hast noch keine Fahrzeuge angelegt.</div>
<?php else: ?>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Marke</th>
                    <th>Modell</th>
                    <th>Kennzeichen</th>
                    <th>Erstzulassung</th>
                    <th class="text-end">Aktionen</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($vehicles as $vehicle): ?>
                <tr>
                    <td><?php echo Util::escape($vehicle->getBrand()); ?></td>
                    <td><?php echo Util::escape($vehicle->getModel()); ?></td>
                    <td><?php echo Util::escape($vehicle->getLicensePlate()); ?></td>
                    <td><?php echo Util::escape($vehicle->getInitRegistration()->format('d.m.Y')); ?></td>
                    <td class="text-end text-nowrap">
                        <a href="index.php?view=vehicle-form&id=<?php echo $vehicle->getId(); ?>"
                           class="btn btn-sm btn-outline-secondary">Bearbeiten</a>
                        <form method="post" action="index.php" class="d-inline"
                              onsubmit="return confirm('Dieses Fahrzeug wirklich löschen?');">
                            <input type="hidden" name="<?php echo VehicleController::ACTION; ?>" value="<?php echo VehicleController::ACTION_DELETE; ?>">
                            <input type="hidden" name="id" value="<?php echo $vehicle->getId(); ?>">
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

<?php require_once 'views/partials/footer.php'; ?>
