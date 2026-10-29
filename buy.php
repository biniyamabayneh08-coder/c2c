<?php
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$buyerId = (int) $_SESSION['user_id'];
$productId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$productId) {
    exit('Invalid product.');
}

try {
    $conn->beginTransaction();

    $stmt = $conn->prepare(
        "SELECT id, seller_id, price, status
         FROM products
         WHERE id = ?"
    );
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        throw new Exception('Product not found.');
    }

    if ($product['seller_id'] == $buyerId) {
        throw new Exception('You cannot buy your own product.');
    }

    if ($product['status'] !== 'Available') {
        throw new Exception('This product is no longer available.');
    }

    $stmt = $conn->prepare(
        "SELECT id FROM transactions
         WHERE product_id = ?
         AND payment_status IN ('unpaid', 'paid')
         LIMIT 1"
    );
    $stmt->execute([$productId]);

    if ($stmt->fetch()) {
        throw new Exception('This product already has a pending purchase.');
    }

    $totalAmount = (float) $product['price'];
    $commissionAmount = round($totalAmount * 0.10, 2);
    $sellerPayout = round($totalAmount - $commissionAmount, 2);

    $stmt = $conn->prepare(
        "INSERT INTO transactions
        (product_id, buyer_id, seller_id, total_amount, commission_amount, seller_payout, payment_status)
        VALUES (?, ?, ?, ?, ?, ?, 'unpaid')"
    );
    $stmt->execute([
        $productId,
        $buyerId,
        $product['seller_id'],
        $totalAmount,
        $commissionAmount,
        $sellerPayout
    ]);

    $transactionId = $conn->lastInsertId();

    $paymentReference = 'PAY-' . $transactionId . '-' . bin2hex(random_bytes(5));

    $stmt = $conn->prepare(
        "INSERT INTO payments
        (transaction_id, buyer_id, payment_reference, provider, amount, currency, status)
        VALUES (?, ?, ?, 'manual', ?, 'ETB', 'pending')"
    );
   
    $stmt->execute([$transactionId, $buyerId, $paymentReference, $totalAmount]);

    $conn->commit();

} catch (Exception $e) {
    $conn->rollBack();
    exit('Purchase could not be created: ' . e($e->getMessage()));
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment Pending</title>
</head>
<body>
    <h2>Purchase created</h2>
    <p>Your payment is waiting.</p>
    <p><strong>Payment reference:</strong> <?php echo e($paymentReference); ?></p>
    <p>Later, Chapa will replace the “manual” payment step.</p>
    <a href="dashboard.php">Go to dashboard</a>
</body>
</html>