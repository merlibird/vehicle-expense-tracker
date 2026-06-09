<?php
declare(strict_types=1);

use VehicleTracker\Util;

/**
 * Renders and clears pending flash messages (read-once).
 */
$errors  = Util::takeErrors();
$success = Util::takeSuccess();
?>
<?php foreach ($success as $message): ?>
    <div class="alert alert-success" role="alert"><?php echo Util::escape($message); ?></div>
<?php endforeach; ?>
<?php foreach ($errors as $message): ?>
    <div class="alert alert-danger" role="alert"><?php echo Util::escape($message); ?></div>
<?php endforeach; ?>
