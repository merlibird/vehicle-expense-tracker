<?php
declare(strict_types=1);

namespace VehicleTracker;

/**
 * Computes the average fuel consumption (l/100 km) for a vehicle from its fuel
 * bookings, using the full-to-full method: the fuel added between the first and
 * the last recorded odometer reading is what was consumed over that distance.
 */
class ConsumptionCalculator {

    /**
     * @param Expense[] $expenses a vehicle's (active) expenses
     * @return float|null l/100 km rounded to 1 decimal, or null if not computable
     */
    public static function averagePer100Km(array $expenses): ?float {
        $fuel = [];
        foreach ($expenses as $expense) {
            if ($expense instanceof FuelExpense && $expense->getMileage() !== null) {
                $fuel[] = $expense;
            }
        }

        // Need at least two fuel-ups with an odometer reading.
        if (count($fuel) < 2) {
            return null;
        }

        usort($fuel, static fn(FuelExpense $a, FuelExpense $b) => $a->getMileage() <=> $b->getMileage());

        $distance = $fuel[count($fuel) - 1]->getMileage() - $fuel[0]->getMileage();
        if ($distance <= 0) {
            return null;
        }

        // Litres of every fuel-up except the first (which filled the tank at the starting odometer reading).
        $liters = 0.0;
        for ($i = 1, $n = count($fuel); $i < $n; $i++) {
            $liters += $fuel[$i]->getLiters();
        }

        return round($liters / $distance * 100, 1);
    }
}
