<?php
declare(strict_types=1);

namespace VehicleTracker;

/**
 * Wraps one page of a larger result set together with the paging metadata
 * (offset, page size, total count) needed to render a pager.
 *
 * @template T
 */
class PagingResult {

    /**
     * @param array    $result     items on the current page
     * @param int      $offset     zero-based index of the first item on this page
     * @param int      $perPage    page size used to produce this result
     * @param int      $totalCount total number of matching items across all pages
     */
    public function __construct(
        private readonly array $result,
        private readonly int $offset,
        private readonly int $perPage,
        private readonly int $totalCount,
    ) {}

    /** @return array items on the current page */
    public function getResult(): array {
        return $this->result;
    }

    public function getOffset(): int {
        return $this->offset;
    }

    public function getPerPage(): int {
        return $this->perPage;
    }

    public function getTotalCount(): int {
        return $this->totalCount;
    }

    /** 1-based number of the current page. */
    public function getCurrentPage(): int {
        return $this->perPage > 0 ? (int)floor($this->offset / $this->perPage) + 1 : 1;
    }

    /** Total number of pages (at least 1, even when empty). */
    public function getPageCount(): int {
        return $this->perPage > 0 ? max(1, (int)ceil($this->totalCount / $this->perPage)) : 1;
    }

    /** 1-based position of the first item on this page (0 when empty). */
    public function getPositionOfFirst(): int {
        return $this->totalCount === 0 ? 0 : $this->offset + 1;
    }

    /** 1-based position of the last item on this page. */
    public function getPositionOfLast(): int {
        return $this->offset + count($this->result);
    }

    public function hasMultiplePages(): bool {
        return $this->getPageCount() > 1;
    }
}
