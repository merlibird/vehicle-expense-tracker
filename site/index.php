<?php
declare(strict_types=1);

require_once 'inc/bootstrap.php';

// Define which views are public, protected (logged-in users) and admin-only.
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

// Check if the requested view is valid, otherwise default to login/dashboard
$view = $_GET['view'] ?? '';
if (!in_array($view, $allViews, true)) {
    $view = $auth->isLoggedIn() ? 'dashboard' : 'login';
}

// Redirect logged-in users away to dashboard
if (in_array($view, $publicViews, true) && $auth->isLoggedIn()) {
    VehicleTracker\Util::redirect('index.php?view=dashboard');
}

// Require authentication for protected views
if (in_array($view, $protectedViews, true)) {
    $auth->requireUser();
}

// Require admin role for admin views
if (in_array($view, $adminViews, true)) {
    $auth->requireAdmin();
}

$currentUser = $auth->getCurrentUser();

require_once 'views/' . $view . '.php';
