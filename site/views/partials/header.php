<?php
declare(strict_types=1);

use VehicleTracker\Controller\AuthController;
use VehicleTracker\Util;

/**
 * Shared page head + top bar. For logged-in users it also opens the app shell
 * (left sidebar + main content); footer.php closes whatever was opened here.
 * Expects from the including scope (index.php): $view, $currentUser, $auth.
 */
$loggedIn = $auth->isLoggedIn();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fahrzeugkosten-Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="assets/main.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-dark bg-dark fixed-top shadow-sm">
    <div class="container-fluid">
        <?php if ($loggedIn): ?>
            <button class="navbar-toggler border-0 d-md-none me-2" type="button"
                    data-bs-toggle="offcanvas" data-bs-target="#appSidebar"
                    aria-controls="appSidebar" aria-label="Menü öffnen">
                <span class="navbar-toggler-icon"></span>
            </button>
        <?php endif; ?>

        <a class="navbar-brand me-auto" href="index.php">
            <span class="bi bi-fuel-pump" aria-hidden="true"></span>
            Fahrzeugkosten-Tracker
        </a>

        <?php if ($loggedIn && $currentUser !== null): ?>
            <a href="index.php?view=profile" class="navbar-text me-3 d-none d-sm-flex align-items-center text-light text-decoration-none">
                <?php echo Util::escape($currentUser->getFullName()); ?>
            </a>
            <form method="post" action="index.php" class="d-inline">
                <input type="hidden" name="<?php echo AuthController::ACTION; ?>" value="<?php echo AuthController::ACTION_LOGOUT; ?>">
                <button type="submit" class="btn btn-sm btn-outline-light">Abmelden</button>
            </form>
        <?php else: ?>
            <a class="btn btn-sm <?php echo $view === 'login' ? 'btn-light' : 'btn-outline-light'; ?> me-2" href="index.php?view=login">Anmelden</a>
            <a class="btn btn-sm <?php echo $view === 'register' ? 'btn-light' : 'btn-outline-light'; ?>" href="index.php?view=register">Registrieren</a>
        <?php endif; ?>
    </div>
</nav>

<?php if ($loggedIn): ?>
<div class="app-body">
    <aside class="app-sidebar offcanvas-md offcanvas-start" tabindex="-1" id="appSidebar" aria-labelledby="appSidebarLabel">
        <div class="offcanvas-header d-md-none">
            <h5 class="offcanvas-title" id="appSidebarLabel">Menü</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#appSidebar" aria-label="Schließen"></button>
        </div>
        <div class="offcanvas-body p-0 d-flex flex-column">
            <ul class="nav nav-pills flex-column p-3 gap-1 w-100">
                <li class="nav-item">
                    <a class="nav-link <?php echo $view === 'dashboard' ? 'active' : ''; ?>" href="index.php?view=dashboard">
                        <span class="bi bi-speedometer2 me-2" aria-hidden="true"></span>Übersicht
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo in_array($view, ['vehicles', 'vehicle-form'], true) ? 'active' : ''; ?>" href="index.php?view=vehicles">
                        <span class="bi bi-car-front me-2" aria-hidden="true"></span>Fahrzeuge
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo in_array($view, ['expenses', 'expense-form'], true) ? 'active' : ''; ?>" href="index.php?view=expenses">
                        <span class="bi bi-receipt me-2" aria-hidden="true"></span>Ausgaben
                    </a>
                </li>
                <?php if ($currentUser !== null && $currentUser->isAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $view === 'admin' ? 'active' : ''; ?>" href="index.php?view=admin">
                            <span class="bi bi-people me-2" aria-hidden="true"></span>Admin
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

            <ul class="nav nav-pills flex-column p-3 gap-1 w-100 mt-auto border-top">
                <li class="nav-item">
                    <a class="nav-link <?php echo $view === 'profile' ? 'active' : ''; ?>" href="index.php?view=profile">
                        <span class="bi bi-person-gear me-2" aria-hidden="true"></span>Profil
                    </a>
                </li>
            </ul>
        </div>
    </aside>
    <main class="app-main">
<?php else: ?>
    <main class="page-content container">
<?php endif; ?>
