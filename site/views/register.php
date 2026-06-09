<?php
declare(strict_types=1);

use VehicleTracker\Controller\AuthController;
use VehicleTracker\Util;

$old = Util::takeOld();

require_once 'views/partials/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">
        <h1 class="h3 mb-4 text-center">Registrieren</h1>

        <?php require_once 'views/partials/flash.php'; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <form method="post" action="index.php">
                    <input type="hidden" name="<?php echo AuthController::ACTION; ?>" value="<?php echo AuthController::ACTION_REGISTER; ?>">

                    <div class="mb-3">
                        <label for="userName" class="form-label">Benutzername</label>
                        <input type="text" class="form-control" id="userName" name="userName"
                               value="<?php echo Util::escape($old['userName'] ?? ''); ?>"
                               autocomplete="username" autofocus required>
                    </div>

                    <div class="row">
                        <div class="col-sm-6 mb-3">
                            <label for="firstName" class="form-label">Vorname</label>
                            <input type="text" class="form-control" id="firstName" name="firstName"
                                   value="<?php echo Util::escape($old['firstName'] ?? ''); ?>"
                                   autocomplete="given-name" required>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <label for="lastName" class="form-label">Nachname</label>
                            <input type="text" class="form-control" id="lastName" name="lastName"
                                   value="<?php echo Util::escape($old['lastName'] ?? ''); ?>"
                                   autocomplete="family-name" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Passwort</label>
                        <input type="password" class="form-control" id="password" name="password"
                               autocomplete="new-password" minlength="8" required>
                        <div class="form-text">Mindestens 8 Zeichen.</div>
                    </div>

                    <div class="mb-3">
                        <label for="passwordConfirm" class="form-label">Passwort bestätigen</label>
                        <input type="password" class="form-control" id="passwordConfirm" name="passwordConfirm"
                               autocomplete="new-password" minlength="8" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Konto erstellen</button>
                </form>
            </div>
        </div>

        <p class="text-center text-muted mt-3 mb-0">
            Bereits registriert? <a href="index.php?view=login">Zur Anmeldung</a>
        </p>
    </div>
</div>

<?php require_once 'views/partials/footer.php'; ?>
