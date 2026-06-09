<?php
declare(strict_types=1);

use VehicleTracker\Util;

/**
 * Renders "X–Y von Z" plus Bootstrap pagination links.
 * Expects in scope:
 * @var \VehicleTracker\PagingResult $paging
 * @var array<string,mixed>         $pagingBaseParams  GET params to keep in the page links (without 'page')
 */
if ($paging->getTotalCount() === 0) {
    return;
}

$pageCount   = $paging->getPageCount();
$currentPage = $paging->getCurrentPage();
$linkFor     = static fn(int $p): string => 'index.php?' . http_build_query($pagingBaseParams + ['page' => $p]);
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
    <span class="text-muted small">
        <?php echo $paging->getPositionOfFirst(); ?>–<?php echo $paging->getPositionOfLast(); ?>
        von <?php echo $paging->getTotalCount(); ?>
    </span>
    <?php if ($pageCount > 1): ?>
        <nav aria-label="Seitennavigation">
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="<?php echo $currentPage <= 1 ? '#' : Util::escape($linkFor($currentPage - 1)); ?>"
                       aria-label="Zurück" <?php echo $currentPage <= 1 ? 'tabindex="-1" aria-disabled="true"' : ''; ?>>&laquo;</a>
                </li>
                <?php for ($p = 1; $p <= $pageCount; $p++): ?>
                    <li class="page-item <?php echo $p === $currentPage ? 'active' : ''; ?>">
                        <a class="page-link" href="<?php echo Util::escape($linkFor($p)); ?>"><?php echo $p; ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?php echo $currentPage >= $pageCount ? 'disabled' : ''; ?>">
                    <a class="page-link" href="<?php echo $currentPage >= $pageCount ? '#' : Util::escape($linkFor($currentPage + 1)); ?>"
                       aria-label="Weiter" <?php echo $currentPage >= $pageCount ? 'tabindex="-1" aria-disabled="true"' : ''; ?>>&raquo;</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>
