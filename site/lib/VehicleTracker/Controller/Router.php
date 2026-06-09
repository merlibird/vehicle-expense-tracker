<?php
declare(strict_types=1);

namespace VehicleTracker\Controller;

use VehicleTracker\Util;

/**
 * Front-controller dispatch for POST actions: asks each registered controller
 * whether it handles the submitted action and lets the first match dispatch.
 * A matching action always redirects, so control never returns here; an
 * unknown action falls back to a safe redirect.
 */
class Router {

    /** @param AbstractController[] $controllers */
    public function __construct(
        private readonly array $controllers,
    ) {}

    public function handlePost(): void {
        $action = $_POST[AbstractController::ACTION] ?? null;
        if ($action === null) {
            return;
        }

        foreach ($this->controllers as $controller) {
            if ($controller->handles($action)) {
                $controller->dispatch($action);
            }
        }

        Util::redirect('index.php');
    }
}
