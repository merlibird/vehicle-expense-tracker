<?php
declare(strict_types=1);

require_once 'inc/bootstrap.php';

$publicViews    = ['login', 'register'];
$protectedViews = ['dashboard', 'vehicles', 'vehicle-form', 'expenses', 'expense-form', 'profile'];
$adminViews     = ['admin'];
$allViews       = array_merge($publicViews, $protectedViews, $adminViews);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $router->handlePost();
}

// Log out logged-in accounts that have since been deactivated (e.g. by an admin).
if ($auth->isLoggedIn()) {
    $sessionUser = $auth->getCurrentUser();
    if ($sessionUser === null || !$sessionUser->isActive()) {
        VehicleTracker\SessionContext::clear();
        VehicleTracker\Util::setError('Dein Konto wurde deaktiviert.');
        VehicleTracker\Util::redirect('index.php?view=login');
    }
}

$view = $_GET['view'] ?? ($auth->isLoggedIn() ? 'dashboard' : 'login');
if (!in_array($view, $allViews, true)) {
    $view = $auth->isLoggedIn() ? 'dashboard' : 'login';
}

if (in_array($view, $publicViews, true) && $auth->isLoggedIn()) {
    VehicleTracker\Util::redirect('index.php?view=dashboard');
}

if (in_array($view, $protectedViews, true)) {
    $auth->requireUser();
}

if (in_array($view, $adminViews, true)) {
    $auth->requireAdmin();
}

$currentUser = $auth->getCurrentUser();

require_once 'views/' . $view . '.php';
