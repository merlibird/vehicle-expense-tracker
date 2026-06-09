<?php
declare(strict_types=1);

use VehicleTracker\CategoryColor;
use VehicleTracker\Util;

/**
 * Renders a single category badge in its palette colour.
 * Expects in scope:
 * @var \VehicleTracker\ExpenseCategory $category
 * @var array<int,string>              $catColors  category id => hex colour
 */
?>
<span class="badge" style="background-color: <?php echo $catColors[$category->getId()] ?? CategoryColor::FALLBACK; ?>;"><?php echo Util::escape($category->getName()); ?></span>
