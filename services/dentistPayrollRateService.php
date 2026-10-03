<?php
session_start();
header('Content-Type: application/json');

require_once(__DIR__ . '/databaseService.php');
require_once(__DIR__ . '/DentistPayrollRateStore.php');
require_once(__DIR__ . '/../bars/properties.php');

try {
    if (!in_array((int) ($_SESSION['account_type'] ?? -1), [0, 1, 100], true)) {
        http_response_code(403);
        throw new RuntimeException('You do not have permission to update payroll rates.');
    }

    $action = $_POST['action'] ?? '';
    $dentist = trim((string) ($_POST['dentist'] ?? ''));
    if (!in_array($action, ['get', 'save'], true) || $dentist === '') {
        throw new InvalidArgumentException('Select a valid dentist.');
    }

    $database = new Database();
    $rateStore = new DentistPayrollRateStore($database->dbConnection(), $dentistRate);
    if (!$rateStore->hasDentist($dentist)) {
        throw new InvalidArgumentException('The selected dentist has no configured payroll rate.');
    }

    if ($action === 'save') {
        $rate = (string) ($_POST['rate'] ?? '');
        if (!preg_match('/^\d{1,7}(?:\.\d{1,3})?$/', $rate)) {
            throw new InvalidArgumentException('Enter a rate from 0 to 9,999,999.999 with up to 3 decimal places.');
        }
        $rateStore->saveRate($dentist, (float) $rate);
    }

    echo json_encode([
        'success' => true,
        'rate' => $rateStore->getRate($dentist),
    ]);
} catch (Throwable $error) {
    if (http_response_code() < 400) {
        http_response_code(400);
    }
    echo json_encode([
        'success' => false,
        'message' => $error->getMessage(),
    ]);
}
?>