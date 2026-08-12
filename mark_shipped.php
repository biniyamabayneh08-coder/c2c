<?php
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$sellerId = (int) $_SESSION['user_id'];
$transactionId = (int) ($_GET['transaction_id'] ?? 0);

if ($transactionId <= 0) {
    exit('Invalid transaction.');
}

$stmt = $conn->prepare(
    "SELECT id
     FROM transactions
     WHERE id = ?
       AND seller_id = ?
       AND payment_status = 'paid'
       AND delivery_status = 'waiting_for_shipping'"
);
$stmt->bind_param("ii", $transactionId, $sellerId);
$stmt->execute();

if (!$stmt->get_result()->fetch_assoc()) {
    exit('You cannot mark this item as shipped.');
}

$stmt = $conn->prepare(
    "UPDATE transactions
     SET delivery_status = 'shipped'
     WHERE id = ?"
);
$stmt->bind_param("i", $transactionId);
$stmt->execute();

echo '<h2>Item marked as shipped</h2>';
echo '<p>The buyer can now confirm that they received it.</p>';