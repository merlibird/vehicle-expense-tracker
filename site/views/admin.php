<?php
declare(strict_types=1);

use VehicleTracker\Controller\AdminController;
use VehicleTracker\Util;

$users = $userDao->getAll();

require_once 'views/partials/header.php';
?>

<h1 class="h3 mb-3">Benutzerverwaltung</h1>

<?php require_once 'views/partials/flash.php'; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
        <thead>
            <tr>
                <th>Benutzername</th>
                <th>Name</th>
                <th>Rolle</th>
                <th>Status</th>
                <th class="text-end">Aktion</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <?php $isSelf = $user->getId() === $currentUser->getId(); ?>
            <tr>
                <td><?php echo Util::escape($user->getUserName()); ?></td>
                <td><?php echo Util::escape($user->getFullName()); ?></td>
                <td>
                    <span class="badge <?php echo $user->isAdmin() ? 'text-bg-primary' : 'text-bg-secondary'; ?>">
                        <?php echo Util::escape($user->getRole()->value); ?>
                    </span>
                </td>
                <td>
                    <?php if ($user->isActive()): ?>
                        <span class="badge text-bg-success">Aktiv</span>
                    <?php else: ?>
                        <span class="badge text-bg-secondary">Inaktiv</span>
                    <?php endif; ?>
                </td>
                <td class="text-end">
                    <?php if ($isSelf): ?>
                        <span class="text-muted small">(du)</span>
                    <?php else: ?>
                        <form method="post" action="index.php" class="d-inline">
                            <input type="hidden" name="<?php echo AdminController::ACTION; ?>" value="<?php echo AdminController::ACTION_SET_ACTIVE; ?>">
                            <input type="hidden" name="id" value="<?php echo $user->getId(); ?>">
                            <?php if ($user->isActive()): ?>
                                <input type="hidden" name="active" value="0">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Deaktivieren</button>
                            <?php else: ?>
                                <input type="hidden" name="active" value="1">
                                <button type="submit" class="btn btn-sm btn-outline-success">Aktivieren</button>
                            <?php endif; ?>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'views/partials/footer.php'; ?>
