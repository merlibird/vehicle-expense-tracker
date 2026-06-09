<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', 'On');

// Autoloader — maps namespace to file path under lib/
spl_autoload_register(function (string $class): void {
    $file = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR
          . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

VehicleTracker\SessionContext::start();

// In production these credentials should come from environment variables or a
// config file outside the web root, not be hard-coded here.
$host   = 'db';
$dbName = 'db';
$user   = 'db';
$pass   = 'db';
$dsn    = "mysql:host=$host;dbname=$dbName;charset=utf8mb4";
$dbConnection = new Data\DatabaseConnection($dsn, $user, $pass);

// DAOs for database access, each wired with the shared DB connection.
$userDao     = new Data\Dao\UserDao($dbConnection);
$vehicleDao  = new Data\Dao\VehicleDao($dbConnection);
$expenseDao  = new Data\Dao\ExpenseDao($dbConnection);
$categoryDao = new Data\Dao\CategoryDao($dbConnection);
$logDao      = new Data\Dao\LogDao($dbConnection);

// Authentication manager, wired with the User DAO and Log DAO for login/logout.
$auth = new VehicleTracker\AuthenticationManager($userDao, $logDao);

// POST-action controllers, each wired with the DAOs it needs.
$authController    = new VehicleTracker\Controller\AuthController($auth, $logDao);
$vehicleController = new VehicleTracker\Controller\VehicleController($auth, $logDao, $vehicleDao);
$expenseController = new VehicleTracker\Controller\ExpenseController($auth, $logDao, $vehicleDao, $expenseDao, $categoryDao);
$adminController   = new VehicleTracker\Controller\AdminController($auth, $logDao, $userDao);
$profileController = new VehicleTracker\Controller\ProfileController($auth, $logDao, $userDao);

// Router dispatches a POST action to the responsible controller
$router = new VehicleTracker\Controller\Router([$authController, $vehicleController, $expenseController, $adminController, $profileController]);
