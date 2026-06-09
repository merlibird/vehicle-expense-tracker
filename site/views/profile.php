<?php
declare(strict_types=1);

use VehicleTracker\Controller\ProfileController;
use VehicleTracker\Util;

$old = Util::takeOld();

// Profile fields: from $old after an error, otherwise from the current user.
$userName  = $old['userName']  ?? $currentUser->getUserName();
$firstName = $old['firstName'] ?? $currentUser->getFirstName();
$lastName  = $old['lastName']  ?? $currentUser->getLastName();

require_once 'views/partials/header.php';
?>

<h1 class="h3 mb-4">Mein Profil</h1>

<?php require_once 'views/partials/flash.php'; ?>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h5 card-title mb-3">Stammdaten</h2>
                <form method="post" action="index.php">
                    <input type="hidden" name="<?php echo ProfileController::ACTION; ?>" value="<?php echo ProfileController::ACTION_UPDATE; ?>">

                    <div class="mb-3">
                        <label for="userName" class="form-label">Benutzername</label>
                        <input type="text" class="form-control" id="userName" name="userName" maxlength="50"
                               value="<?php echo Util::escape($userName); ?>" required>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 mb-3">
                            <label for="firstName" class="form-label">Vorname</label>
                            <input type="text" class="form-control" id="firstName" name="firstName" maxlength="100"
                                   value="<?php echo Util::escape($firstName); ?>" required>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <label for="lastName" class="form-label">Nachname</label>
                            <input type="text" class="form-control" id="lastName" name="lastName" maxlength="100"
                                   value="<?php echo Util::escape($lastName); ?>" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Speichern</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h5 card-title mb-3">Passwort ändern</h2>
                <form method="post" action="index.php">
                    <input type="hidden" name="<?php echo ProfileController::ACTION; ?>" value="<?php echo ProfileController::ACTION_PASSWORD; ?>">

                    <div class="mb-3">
                        <label for="currentPassword" class="form-label">Aktuelles Passwort</label>
                        <input type="password" class="form-control" id="currentPassword" name="currentPassword"
                               autocomplete="current-password" required>
                    </div>
                    <div class="mb-3">
                        <label for="newPassword" class="form-label">Neues Passwort</label>
                        <input type="password" class="form-control" id="newPassword" name="newPassword"
                               autocomplete="new-password" minlength="8" required>
                        <div class="form-text">Mindestens 8 Zeichen.</div>
                    </div>
                    <div class="mb-3">
                        <label for="newPasswordConfirm" class="form-label">Neues Passwort bestätigen</label>
                        <input type="password" class="form-control" id="newPasswordConfirm" name="newPasswordConfirm"
                               autocomplete="new-password" minlength="8" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Passwort ändern</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'views/partials/footer.php'; ?>
