<?php
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$buyerId = (int) $_SESSION['user_id'];
$transactionId = (int) ($_GET['transaction_id'] ?? 0);

if ($transactionId <= 0) {
    exit('Invalid transaction.');
}

try {
    $conn->begin_transaction();

    $stmt = $conn->prepare(
        "SELECT
            p.id AS payment_id,
            p.status AS payment_status,
            t.product_id,
            t.seller_id,
            t.seller_payout,
            t.commission_amount
         FROM payments p
         JOIN transactions t ON t.id = p.transaction_id
         WHERE t.id = ? AND t.buyer_id = ?"
    );
    $stmt->bind_param("ii", $transactionId, $buyerId);
    $stmt->execute();
    $payment = $stmt->get_result()->fetch_assoc();

    if (!$payment) {
        throw new Exception('Payment not found.');
    }

    if ($payment['payment_status'] === 'pending') {
        $stmt = $conn->prepare(
            "UPDATE payments
             SET status = 'paid', paid_at = NOW()
             WHERE id = ?"
        );
        $stmt->bind_param("i", $payment['payment_id']);
        $stmt->execute();

        $stmt = $conn->prepare(
            "UPDATE transactions
            SET payment_status = 'paid',
         delivery_status = 'waiting_for_shipping'
     WHERE id = ?"
           
        );
        $stmt->bind_param("i", $transactionId);
        $stmt->execute();

        $message = 'Test payment successful.';
    } elseif ($payment['payment_status'] === 'paid') {
        $message = 'This payment was already successful.';
    } else {
        throw new Exception('This payment cannot be confirmed.');
    }

    $stmt = $conn->prepare(
        "UPDATE products
         SET status = 'Sold'
         WHERE id = ?"
    );
    $stmt->bind_param("i", $payment['product_id']);
    $stmt->execute();

    /* Create the seller's waiting payout only once */
    $stmt = $conn->prepare(
        "SELECT id FROM seller_payouts
         WHERE transaction_id = ?
         LIMIT 1"
    );
    $stmt->bind_param("i", $transactionId);
    $stmt->execute();
    $existingPayout = $stmt->get_result()->fetch_assoc();

    if (!$existingPayout) {
        $stmt = $conn->prepare(
            "INSERT INTO seller_payouts
            (transaction_id, seller_id, amount, marketplace_fee, status)
            VALUES (?, ?, ?, ?, 'waiting')"
        );
        $stmt->bind_param(
            "iidd",
            $transactionId,
            $payment['seller_id'],
            $payment['seller_payout'],
            $payment['commission_amount']
        );
        $stmt->execute();
    }

    $conn->commit();

} catch (Exception $e) {
    $conn->rollback();
    exit('Payment could not be confirmed: ' . e($e->getMessage()));
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Test Payment Complete</title>
</head>
<body>
    <h2><?php echo e($message); ?></h2>
    <p>The product is Sold, and the seller payout is waiting.</p>
</body>
</html>