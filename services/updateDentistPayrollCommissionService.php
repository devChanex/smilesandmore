<?php
require_once(__DIR__ . '/databaseService.php');

header('Content-Type: application/json');

try {
    $paymentId = $_POST['tsubpayid'] ?? '';
    $isTax = $_POST['isTax'] ?? '';
    $labFeeValue = $_POST['lab_fee'] ?? '';
    $commissionRate = $_POST['commision_rate'] ?? '';
    $commissionValue = $_POST['commision'] ?? '';

    if (!ctype_digit((string) $paymentId) || (int) $paymentId < 1) {
        throw new InvalidArgumentException('Invalid payment record.');
    }
    if (!in_array($isTax, ['true', 'false'], true)) {
        throw new InvalidArgumentException('Invalid tax selection.');
    }
    if (!is_numeric($labFeeValue) || (float) $labFeeValue < 0) {
        throw new InvalidArgumentException('Enter a valid lab fee.');
    }
    $validRates = ['0', '5', '10', '15', '20', '25', '30', '35', '40', '45', '50', '55', '60', 'Other'];
    if (!in_array((string) $commissionRate, $validRates, true)) {
        throw new InvalidArgumentException('Select a valid commission rate.');
    }

    $database = new Database();
    $connection = $database->dbConnection();
    $stmt = $connection->prepare('SELECT amount, paymenttype FROM treatmentsubpayment WHERE tsubpayid = :payment_id');
    $stmt->execute([':payment_id' => (int) $paymentId]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$payment) {
        throw new RuntimeException('Payment record not found.');
    }

    $treatmentFee = (float) $payment['amount'];
    $tax = $isTax === 'true' ? round($treatmentFee * 0.10, 2) : 0;
    $isCreditCard = strtolower(trim((string) $payment['paymenttype'])) === 'credit card';
    $subcharge = $isCreditCard ? round($treatmentFee * 0.04, 2) : 0;
    $labFee = round((float) $labFeeValue, 2);

    if ($commissionRate === 'Other') {
        if (!is_numeric($commissionValue) || (float) $commissionValue < 0) {
            throw new InvalidArgumentException('Enter a valid commission amount.');
        }
        $commission = round((float) $commissionValue, 2);
    } else {
        $commission = round(($treatmentFee - $tax - $subcharge - $labFee) * ((float) $commissionRate / 100), 2);
    }

    $stmt = $connection->prepare('UPDATE treatmentsubpayment SET isTax = :is_tax, tax = :tax, subcharge = :subcharge, lab_fee = :lab_fee, commision_rate = :commission_rate, commision = :commission WHERE tsubpayid = :payment_id');
    $stmt->execute([
        ':is_tax' => $isTax,
        ':tax' => $tax,
        ':subcharge' => $subcharge,
        ':lab_fee' => $labFee,
        ':commission_rate' => $commissionRate,
        ':commission' => $commission,
        ':payment_id' => (int) $paymentId,
    ]);

    echo json_encode([
        'success' => true,
        'tax' => $tax,
        'subcharge' => $subcharge,
        'commission' => $commission,
    ]);
} catch (Throwable $error) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $error->getMessage()]);
}
?>