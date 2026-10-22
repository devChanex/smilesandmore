<?php
header('Content-Type: application/json');

$to = isset($_POST['to']) ? trim($_POST['to']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';
$name = isset($_POST['name']) ? trim($_POST['name']) : '';

if (!$to || !$message) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing recipient or message.'
    ]);
    exit;
}

$logFile = dirname(__DIR__) . '/sms-send-log.txt';
$entry = date('Y-m-d H:i:s') . ' | To: ' . $to . ' | Name: ' . $name . ' | Message: ' . $message . PHP_EOL;
file_put_contents($logFile, $entry, FILE_APPEND);

echo json_encode([
    'success' => false,
    'message' => 'No SMS provider is configured. This local-only setup only logs the message for testing.',
    'provider' => 'local-log'
]);
