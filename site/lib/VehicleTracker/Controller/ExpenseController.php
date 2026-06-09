<?php
declare(strict_types=1);

namespace VehicleTracker\Controller;

use Data\Dao\IVehicleDao;
use Data\Dao\IExpenseDao;
use Data\Dao\ICategoryDao;
use Data\Dao\ILogDao;
use VehicleTracker\AuthenticationManager;
use VehicleTracker\Expense;
use VehicleTracker\FuelExpense;
use VehicleTracker\Util;

class ExpenseController extends AbstractController {

    use VehicleOwnership;

    public const string ACTION_SAVE   = 'expense-save';
    public const string ACTION_DELETE = 'expense-delete';

    /** Special category: selecting it turns an expense into a fuel booking. */
    public const string FUEL_CATEGORY = 'Tanken';

    public function __construct(
        AuthenticationManager $auth,
        ILogDao $logDao,
        private readonly IVehicleDao $vehicleDao,
        private readonly IExpenseDao $expenseDao,
        private readonly ICategoryDao $categoryDao,
    ) {
        parent::__construct($auth, $logDao);
    }

    public function handles(string $action): bool {
        return in_array($action, [self::ACTION_SAVE, self::ACTION_DELETE], true);
    }

    public function dispatch(string $action): void {
        match ($action) {
            self::ACTION_SAVE   => $this->saveExpense(),
            self::ACTION_DELETE => $this->deleteExpense(),
        };
    }

    private function saveExpense(): never {
        $user   = $this->auth->requireUser();
        $id     = (int)($_POST['id'] ?? 0);
        $isEdit = $id > 0;

        // When editing, load the existing booking and check ownership. The fuel
        // flag is fixed after creation (the DAO cannot toggle it).
        $existing = null;
        if ($isEdit) {
            $existing = $this->ownedExpense($id, $user->getId());
            if ($existing === null) {
                Util::setError('Buchung nicht gefunden.');
                Util::redirect('index.php?view=expenses');
            }
        }

        $vehicleId  = (int)($_POST['vehicleId'] ?? 0);
        $dateInput  = trim($_POST['date'] ?? '');
        $costInput  = trim($_POST['cost'] ?? '');
        $mileageIn  = trim($_POST['mileage'] ?? '');
        $note       = trim($_POST['note'] ?? '');
        $litersIn   = trim($_POST['liters'] ?? '');
        $priceIn    = trim($_POST['pricePerLiter'] ?? '');
        $categories = $this->filterValidCategoryIds($_POST['categories'] ?? []);

        // The 'Tanken' category decides whether this is a fuel booking.
        $fuelCategory = $this->categoryDao->getByName(self::FUEL_CATEGORY);
        $fuelCatId    = $fuelCategory?->getId();
        $isFuel       = $isEdit
            ? ($existing instanceof FuelExpense)
            : ($fuelCatId !== null && in_array($fuelCatId, $categories, true));

        $errors = [];

        if (!$this->ownsVehicle($vehicleId, $user->getId())) {
            $errors[] = 'Bitte ein gültiges Fahrzeug auswählen.';
        }

        $date = Util::parseDate($dateInput);
        if ($date === null) {
            $errors[] = 'Bitte ein gültiges Datum angeben.';
        } elseif ($date > new \DateTimeImmutable('today')) {
            $errors[] = 'Das Datum darf nicht in der Zukunft liegen.';
        }

        $cost = Util::parseDecimal($costInput);
        if ($cost === null || $cost <= 0) {
            $errors[] = 'Bitte einen gültigen Betrag (größer 0) angeben.';
        }

        $mileage = null;
        if ($mileageIn !== '') {
            if (!ctype_digit($mileageIn)) {
                $errors[] = 'Der Kilometerstand muss eine ganze Zahl sein.';
            } else {
                $mileage = (int)$mileageIn;
            }
        }

        if (mb_strlen($note) > 500) {
            $errors[] = 'Die Notiz darf höchstens 500 Zeichen lang sein.';
        }

        // At least one category is required. Fuel bookings get 'Tanken'
        // automatically (see the invariant below), so they need no extra choice.
        if (!$isFuel && count($categories) === 0) {
            $errors[] = 'Bitte mindestens eine Kategorie auswählen.';
        }

        $liters = null;
        $price  = null;
        if ($isFuel) {
            $liters = Util::parseDecimal($litersIn);
            $price  = Util::parseDecimal($priceIn);
            if ($liters === null || $liters <= 0) {
                $errors[] = 'Bitte eine gültige Literzahl (größer 0) angeben.';
            }
            if ($price === null || $price <= 0) {
                $errors[] = 'Bitte einen gültigen Preis pro Liter (größer 0) angeben.';
            }
        }

        if (count($errors) > 0) {
            Util::setErrors($errors);
            Util::setOld([
                'vehicleId'     => (string)$vehicleId,
                'date'          => $dateInput,
                'cost'          => $costInput,
                'mileage'       => $mileageIn,
                'note'          => $note,
                'liters'        => $litersIn,
                'pricePerLiter' => $priceIn,
                'categories'    => array_map('strval', $categories),
            ]);
            $target = $isEdit ? 'index.php?view=expense-form&id=' . $id : 'index.php?view=expense-form';
            Util::redirect($target);
        }

        // Invariant: the 'Tanken' category is set if and only if this is a fuel booking.
        if ($fuelCatId !== null) {
            $categories = array_values(array_filter($categories, static fn($c) => $c !== $fuelCatId));
            if ($isFuel) {
                $categories[] = $fuelCatId;
            }
        }

        $kind = $isFuel ? 'FUEL' : 'EXPENSE';
        if ($isEdit) {
            if ($isFuel) {
                $this->expenseDao->updateFuel($id, $date, $cost, $note ?: null, $mileage, $liters, $price);
            } else {
                $this->expenseDao->update($id, $date, $cost, $note ?: null, $mileage);
            }
            $expenseId = $id;
            $this->logAction($user, $kind . '_UPDATE:' . $expenseId);
            Util::setSuccess('Buchung aktualisiert.');
        } else {
            if ($isFuel) {
                $expenseId = $this->expenseDao->createFuel($vehicleId, $date, $cost, $note ?: null, $mileage, $liters, $price);
            } else {
                $expenseId = $this->expenseDao->create($vehicleId, $date, $cost, $note ?: null, $mileage);
            }
            $this->logAction($user, $kind . '_CREATE:' . $expenseId);
            Util::setSuccess('Buchung hinzugefügt.');
        }

        $this->categoryDao->setForExpense($expenseId, $categories);
        Util::redirect('index.php?view=expenses');
    }

    private function deleteExpense(): never {
        $user = $this->auth->requireUser();
        $id   = (int)($_POST['id'] ?? 0);

        if ($id <= 0 || $this->ownedExpense($id, $user->getId()) === null) {
            Util::setError('Buchung nicht gefunden.');
            Util::redirect('index.php?view=expenses');
        }

        $this->expenseDao->softDelete($id);
        $this->logAction($user, 'EXPENSE_DELETE:' . $id);
        Util::setSuccess('Buchung gelöscht.');
        Util::redirect('index.php?view=expenses');
    }

    /** Returns the active expense if it belongs (via its vehicle) to the user, else null. */
    private function ownedExpense(int $expenseId, int $userId): ?Expense {
        $expense = $this->expenseDao->getById($expenseId);
        if ($expense === null || !$expense->isActive()) {
            return null;
        }
        return $this->ownsVehicle($expense->getVehicleId(), $userId) ? $expense : null;
    }

    /**
     * Keeps only the submitted ids that are real category ids.
     * @param mixed $submitted
     * @return int[]
     */
    private function filterValidCategoryIds(mixed $submitted): array {
        if (!is_array($submitted)) {
            return [];
        }
        $valid = [];
        foreach ($this->categoryDao->getAll() as $category) {
            $valid[$category->getId()] = true;
        }
        $result = [];
        foreach ($submitted as $id) {
            $id = (int)$id;
            if (isset($valid[$id])) {
                $result[$id] = $id; // dedupe
            }
        }
        return array_values($result);
    }
}
