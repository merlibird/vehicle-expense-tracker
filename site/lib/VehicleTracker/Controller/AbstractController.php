<?php
declare(strict_types=1);

namespace VehicleTracker\Controller;

use Data\Dao\ILogDao;
use VehicleTracker\AuthenticationManager;
use VehicleTracker\User;

/**
 * Base for POST-action controllers. Holds the auth dependency and the
 * dispatch contract; the Router asks each controller whether it handles
 * an action and then lets it dispatch (every action ends in a redirect).
 */
abstract class AbstractController {

    /** Name of the POST field that selects the action. */
    public const string ACTION = 'action';

    public function __construct(
        protected readonly AuthenticationManager $auth,
        protected readonly ILogDao $logDao,
    ) {}

    /** Whether this controller is responsible for the given action. */
    abstract public function handles(string $action): bool;

    /** Executes the action; every path ends in a redirect. */
    abstract public function dispatch(string $action): void;

    /**
     * Records a user action in the log (IP, action, username, timestamp).
     * Same shape as the auth log entries in AuthenticationManager.
     */
    protected function logAction(User $user, string $action): void {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $this->logDao->create($user->getId(), $user->getUserName(), $ip, $action);
    }
}
