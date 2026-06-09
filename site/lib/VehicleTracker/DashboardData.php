<?php
declare(strict_types=1);

namespace VehicleTracker;

use Data\Dao\ICategoryDao;
use Data\Dao\IExpenseDao;
use Data\Dao\IVehicleDao;

/**
 * View model for the dashboard. Turns the user's vehicles, expenses and the
 * active filter into the ready-to-render values the view needs — all DAO access
 * and computation lives here, so the template only formats and prints.
 */
final class DashboardData {

    private const array MONTH_NAMES = [
        1 => 'Jänner', 2 => 'Februar', 3 => 'März',      4 => 'April',
        5 => 'Mai',    6 => 'Juni',    7 => 'Juli',      8 => 'August',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Dezember',
    ];

    /**
     * @param Vehicle[]                       $vehicles         the user's vehicles (for the filter + count)
     * @param int[]                           $years            years that have bookings, newest first
     * @param array<int, string>              $availableMonths  month number => name, for the selected year
     * @param list<array{name: string, total: float, color: string}> $bars  category breakdown
     * @param Expense[]                       $recent           latest five bookings in scope
     * @param array<int, ExpenseCategory[]>   $recentCategories categories per recent booking id
     * @param array<int, string>              $vehicleNames     vehicle id => display name
     * @param array<int, string>              $catColors        category id => hex colour (for badges)
     */
    private function __construct(
        public readonly array $vehicles,
        public readonly array $catColors,
        public readonly array $years,
        public readonly array $availableMonths,
        public readonly ?int $vehicleId,
        public readonly ?int $year,
        public readonly ?int $month,
        public readonly string $periodLabel,
        public readonly float $periodTotal,
        public readonly array $bars,
        public readonly float $maxCat,
        public readonly array $recent,
        public readonly array $recentCategories,
        public readonly array $vehicleNames,
        public readonly string $recentScope,
        public readonly string $expensesLink,
        public readonly int $bookingCount,
        public readonly ?Vehicle $singleVehicle,
        public readonly ?int $singleLatestKm,
        public readonly int $fleetCount,
        public readonly int $fleetDistance,
        public readonly string $kpiLabel,
        public readonly ?float $kpiConsumption,
        public readonly ?float $kpiCostPerKm,
    ) {}

    public static function build(
        IVehicleDao $vehicleDao,
        IExpenseDao $expenseDao,
        ICategoryDao $categoryDao,
        int $userId,
        ?int $vehicleId,
        ?int $year,
        ?int $month,
    ): self {
        $vehicles   = $vehicleDao->getByUserId($userId);
        $vehicleIds = array_map(static fn(Vehicle $v) => $v->getId(), $vehicles);

        // Validate the filter against the user's own data.
        if ($vehicleId !== null && !in_array($vehicleId, $vehicleIds, true)) {
            $vehicleId = null;
        }

        // The month filter only applies within a year and only for months that have bookings.
        $availableMonths = [];
        if ($year !== null) {
            foreach ($expenseDao->getActiveMonths($userId, $year, $vehicleId) as $num) {
                $availableMonths[$num] = self::MONTH_NAMES[$num];
            }
            if ($month !== null && !isset($availableMonths[$month])) {
                $month = null;
            }
        } else {
            $month = null;
        }

        $years = $expenseDao->getYearsByUser($userId);

        if ($year !== null && $month !== null) {
            $periodLabel = self::MONTH_NAMES[$month] . ' ' . $year;
        } elseif ($year !== null) {
            $periodLabel = (string)$year;
        } else {
            $periodLabel = 'alle Zeiträume';
        }

        $periodTotal = $expenseDao->getTotalByUser($userId, $year, $month, $vehicleId);

        // Category breakdown: every category, 0 € when nothing was booked in the period.
        $categories  = $categoryDao->getAll();
        $catColors   = CategoryColor::map($categories);
        $totalByName = [];
        foreach ($expenseDao->getCategoryTotalsByUser($userId, $year, $month, $vehicleId) as $c) {
            $totalByName[$c['name']] = $c['total'];
        }
        $bars   = [];
        $maxCat = 0.0;
        foreach ($categories as $cat) {
            $total  = $totalByName[$cat->getName()] ?? 0.0;
            $bars[] = [
                'name'  => $cat->getName(),
                'total' => $total,
                'color' => $catColors[$cat->getId()] ?? CategoryColor::FALLBACK,
            ];
            $maxCat = max($maxCat, $total);
        }

        // Bookings in scope: for the count and the latest five (+ their categories in one query).
        $filtered         = $expenseDao->getByUserIdFiltered($userId, $vehicleId, $year, $month, null);
        $bookingCount     = count($filtered);
        $recent           = array_slice($filtered, 0, 5);
        $recentCategories = $categoryDao->getByExpenseIds(array_map(static fn(Expense $e) => $e->getId(), $recent));

        $vehicleNames = [];
        foreach ($vehicles as $v) {
            $vehicleNames[$v->getId()] = $v->getDisplayName();
        }
        $recentScope = ($vehicleId !== null ? ($vehicleNames[$vehicleId] ?? '—') : 'Alle Fahrzeuge') . ' · ' . $periodLabel;

        // "Show all" carries every active filter into the expenses list.
        $query = ['view' => 'expenses'];
        if ($vehicleId !== null) { $query['fVehicle'] = $vehicleId; }
        if ($year !== null)      { $query['fYear']    = $year; }
        if ($month !== null)     { $query['fMonth']   = $month; }
        $expensesLink = 'index.php?' . http_build_query($query);

        // Right card + KPIs: a single vehicle (filtered, or only one exists) OR the whole fleet.
        $expensesByVehicle = $expenseDao->getByVehicleIds($vehicleIds);

        $singleVehicle = null;
        if ($vehicleId !== null) {
            foreach ($vehicles as $v) {
                if ($v->getId() === $vehicleId) { $singleVehicle = $v; }
            }
        } elseif (count($vehicles) === 1) {
            $singleVehicle = $vehicles[0];
        }

        $singleLatestKm = null;
        $fleetDistance  = 0;
        $kpiLabel       = 'alle Fahrzeuge';
        $kpiConsumption = null;
        $kpiCostPerKm   = null;

        if ($singleVehicle !== null) {
            $m              = self::metrics($expensesByVehicle[$singleVehicle->getId()] ?? []);
            $kpiConsumption = $m['consumption'];
            $kpiCostPerKm   = ($m['distance'] !== null && $m['distance'] > 0) ? $m['cost'] / $m['distance'] : null;
            $kpiLabel       = $singleVehicle->getDisplayName();
            $singleLatestKm = $m['latestKm'];
        } else {
            $fleetCost = 0.0;
            $consVals  = [];
            foreach ($vehicles as $v) {
                $m = self::metrics($expensesByVehicle[$v->getId()] ?? []);
                if ($m['distance'] !== null)    { $fleetDistance += $m['distance']; }
                if ($m['consumption'] !== null) { $consVals[] = $m['consumption']; }
                $fleetCost += $m['cost'];
            }
            $kpiConsumption = $consVals !== [] ? round(array_sum($consVals) / count($consVals), 1) : null;
            $kpiCostPerKm   = $fleetDistance > 0 ? $fleetCost / $fleetDistance : null;
        }

        return new self(
            vehicles:         $vehicles,
            catColors:        $catColors,
            years:            $years,
            availableMonths:  $availableMonths,
            vehicleId:        $vehicleId,
            year:             $year,
            month:            $month,
            periodLabel:      $periodLabel,
            periodTotal:      $periodTotal,
            bars:             $bars,
            maxCat:           $maxCat,
            recent:           $recent,
            recentCategories: $recentCategories,
            vehicleNames:     $vehicleNames,
            recentScope:      $recentScope,
            expensesLink:     $expensesLink,
            bookingCount:     $bookingCount,
            singleVehicle:    $singleVehicle,
            singleLatestKm:   $singleLatestKm,
            fleetCount:       count($vehicles),
            fleetDistance:    $fleetDistance,
            kpiLabel:         $kpiLabel,
            kpiConsumption:   $kpiConsumption,
            kpiCostPerKm:     $kpiCostPerKm,
        );
    }

    /**
     * Metrics derived from one vehicle's bookings.
     * @param Expense[] $expenses
     * @return array{consumption: ?float, latestKm: ?int, distance: ?int, cost: float}
     */
    private static function metrics(array $expenses): array {
        $consumption = ConsumptionCalculator::averagePer100Km($expenses);
        $mileages    = [];
        $cost        = 0.0;
        foreach ($expenses as $e) {
            if ($e->getMileage() !== null) { $mileages[] = $e->getMileage(); }
            $cost += $e->getCost();
        }
        return [
            'consumption' => $consumption,
            'latestKm'    => $mileages !== [] ? max($mileages) : null,
            'distance'    => count($mileages) >= 2 ? max($mileages) - min($mileages) : null,
            'cost'        => $cost,
        ];
    }
}
