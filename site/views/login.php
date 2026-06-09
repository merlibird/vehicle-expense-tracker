<?php
declare(strict_types=1);

use VehicleTracker\Controller\AuthController;
use VehicleTracker\Util;

$old = Util::takeOld();

require_once 'views/partials/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <h1 class="h3 mb-4 text-center">Anmelden</h1>

        <?php require_once 'views/partials/flash.php'; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <form method="post" action="index.php">
                    <input type="hidden" name="<?php echo AuthController::ACTION; ?>" value="<?php echo AuthController::ACTION_LOGIN; ?>">

                    <div class="mb-3">
                        <label for="userName" class="form-label">Benutzername</label>
                        <input type="text" class="form-control" id="userName" name="userName"
                               value="<?php echo Util::escape($old['userName'] ?? ''); ?>"
                               autocomplete="username" autofocus required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Passwort</label>
                        <input type="password" class="form-control" id="password" name="password"
                               autocomplete="current-password" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Anmelden</button>
                </form>
            </div>
        </div>

        <p class="text-center text-muted mt-3 mb-0">
            Noch kein Konto? <a href="index.php?view=register">Jetzt registrieren</a>
        </p>
    </div>
</div>

<?php require_once 'views/partials/footer.php'; ?>
