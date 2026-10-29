<?php
require_once 'db.php';

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
    $conn->beginTransaction();

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
    $stmt->execute([$transactionId, $buyerId]);
    $payment = $stmt->fetch();

    if (!$payment) {
        throw new Exception('Payment not found.');
    }

    if ($payment['payment_status'] === 'pending') {
        $stmt = $conn->prepare(
            "UPDATE payments
             SET status = 'paid', paid_at = CURRENT_TIMESTAMP
             WHERE id = ?"
        );
        $stmt->execute([$payment['payment_id']]);

        $stmt = $conn->prepare(
            "UPDATE transactions
             SET payment_status = 'paid',
                 delivery_status = 'waiting_for_shipping'
             WHERE id = ?"
        );
        $stmt->execute([$transactionId]);

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
    $stmt->execute([$payment['product_id']]);

    $stmt = $conn->prepare(
        "SELECT id FROM seller_payouts
         WHERE transaction_id = ?
         LIMIT 1"
    );
    $stmt->execute([$transactionId]);
    $existingPayout = $stmt->fetch();

    if (!$existingPayout) {
        $stmt = $conn->prepare(
            "INSERT INTO seller_payouts
            (transaction_id, seller_id, amount, marketplace_fee, status)
            VALUES (?, ?, ?, ?, 'waiting')"
        );
        $stmt->execute([
            $transactionId,
            $payment['seller_id'],
            $payment['seller_payout'],
            $payment['commission_amount']
        ]);
    }

    $conn->commit();

} catch (Exception $e) {
    $conn->rollBack();
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