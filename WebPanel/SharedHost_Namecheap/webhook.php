<?php
/**
 * RanOnline Web Panel — Automated Payment Webhook / IPN Listener
 * Handles PayPal, PayMongo, Stripe, or Merchant IPN callbacks.
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/game_relay.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

$rawPayload = file_get_contents('php://input');
$headers = getallheaders();

// Log incoming webhook for audit trail
$logFile = __DIR__ . '/logs/webhook_' . date('Y-m-d') . '.log';
if (!is_dir(__DIR__ . '/logs')) {
    @mkdir(__DIR__ . '/logs', 0755, true);
}
file_put_contents($logFile, sprintf("[%s] IP: %s | Payload: %s\n", date('c'), $_SERVER['REMOTE_ADDR'] ?? 'unknown', $rawPayload), FILE_APPEND);

$pdo = DB::connect();

// Process PayPal IPN or JSON Webhook
$data = json_decode($rawPayload, true);
$orderId = '';
$txId = '';
$paymentStatus = '';

if ($data && !empty($data['event_type'])) {
    // PayPal / Stripe webhook event
    if ($data['event_type'] === 'PAYMENT.CAPTURE.COMPLETED' || $data['event_type'] === 'checkout.session.completed') {
        $orderId = $data['resource']['custom_id'] ?? ($data['data']['object']['client_reference_id'] ?? '');
        $txId = $data['resource']['id'] ?? ($data['data']['object']['id'] ?? '');
        $paymentStatus = 'completed';
    }
} elseif (!empty($_POST['custom'])) {
    // Standard PayPal Form IPN
    $orderId = trim($_POST['custom']);
    $txId = trim($_POST['txn_id'] ?? '');
    $paymentStatus = (strtolower($_POST['payment_status'] ?? '') === 'completed') ? 'completed' : 'pending';
}

if (empty($orderId) || $paymentStatus !== 'completed') {
    http_response_code(400);
    exit('Unrecognized or incomplete webhook');
}

// Find pending order
$stmt = $pdo->prepare("SELECT * FROM web_topup_orders WHERE order_id = :oid LIMIT 1 FOR UPDATE");
$pdo->beginTransaction();
$stmt->execute([':oid' => $orderId]);
$order = $stmt->fetch();

if (!$order) {
    $pdo->rollBack();
    http_response_code(404);
    exit('Order not found');
}

if ($order['game_credited'] == 1) {
    $pdo->rollBack();
    exit('Already credited');
}

// Update order status
$upd = $pdo->prepare("UPDATE web_topup_orders SET status = 'completed', transaction_id = :tx WHERE id = :id");
$upd->execute([':tx' => $txId, ':id' => $order['id']]);
$pdo->commit();

// Insert points into game server via safe Relay
$relayRes = GameRelay::insertPoints($order['username'], (int)$order['points'], $orderId);

if (!empty($relayRes['success'])) {
    $uOrd = $pdo->prepare("UPDATE web_topup_orders SET game_credited = 1, credited_at = NOW() WHERE id = :id");
    $uOrd->execute([':id' => $order['id']]);
    echo "OK - Credited successfully";
} else {
    echo "OK - Order completed, point sync pending";
}
