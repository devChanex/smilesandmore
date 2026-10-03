<?php
require_once(__DIR__ . '/databaseService.php');

header('Content-Type: application/json');

try {
    $paymentId = filter_input(INPUT_POST, 'tsubpayid', FILTER_VALIDATE_INT);
    $isTax = $_POST['isTax'] ?? '';
    if (!$paymentId || !in_array($isTax, ['true', 'false'], true)) {
        throw new InvalidArgumentException('Invalid payment tax information.');
    }

    $database = new Database();
    $connection = $database->dbConnection();
    $stmt = $connection->prepare('SELECT amount FROM treatmentsubpayment WHERE tsubpayid = :payment_id');
    $stmt->execute([':payment_id' => $paymentId]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$payment) {
        throw new RuntimeException('Payment record not found.');
    }

    $tax = $isTax === 'true' ? round((float) $payment['amount'] * 0.10, 2) : 0;
    $stmt = $connection->prepare('UPDATE treatmentsubpayment SET isTax = :is_tax, tax = :tax WHERE tsubpayid = :payment_id');
    $stmt->execute([
        ':is_tax' => $isTax,
        ':tax' => $tax,
        ':payment_id' => $paymentId,
    ]);

    echo json_encode(['success' => true, 'tax' => $tax]);
} catch (Throwable $error) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $error->getMessage()]);
}
?>