<?php
declare(strict_types=1);

namespace VehicleTracker;

/**
 * Assigns a stable colour to each category based on its position in the
 * (ordered) category list, so the dashboard bars and the table badges use
 * the same colour per category.
 */
class CategoryColor {

    /** @var string[] */
    private const array PALETTE = ['#0d3b8f', '#7c5cff', '#e8a13a', '#4a89f3', '#5b6b7b', '#2bb673'];

    public const string FALLBACK = '#6c757d';

    /**
     * @param ExpenseCategory[] $categories ordered list (e.g. CategoryDao::getAll())
     * @return array<int, string> category id => hex colour
     */
    public static function map(array $categories): array {
        $map = [];
        $i   = 0;
        foreach ($categories as $category) {
            $map[$category->getId()] = self::PALETTE[$i % count(self::PALETTE)];
            $i++;
        }
        return $map;
    }
}
