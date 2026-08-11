<?php
require 'db.php';

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
    $conn->begin_transaction();

    /* Get the product safely */
    $stmt = $conn->prepare(
        "SELECT id, seller_id, price, status
         FROM products
         WHERE id = ?
         FOR UPDATE"
    );
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();

    if (!$product) {
        throw new Exception('Product not found.');
    }

    if ($product['seller_id'] == $buyerId) {
        throw new Exception('You cannot buy your own product.');
    }

    if ($product['status'] !== 'Available') {
        throw new Exception('This product is no longer available.');
    }

    /* Stop duplicate pending purchases for this item */
    $stmt = $conn->prepare(
        "SELECT id FROM transactions
         WHERE product_id = ?
         AND payment_status IN ('unpaid', 'paid')
         LIMIT 1"
    );
    $stmt->bind_param("i", $productId);
    $stmt->execute();

    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception('This product already has a pending purchase.');
    }

    /* Marketplace keeps 10%; change 0.10 later if needed */
    $totalAmount = (float) $product['price'];
    $commissionAmount = round($totalAmount * 0.10, 2);
    $sellerPayout = round($totalAmount - $commissionAmount, 2);

    /* Create the marketplace transaction */
    $stmt = $conn->prepare(
        "INSERT INTO transactions
        (product_id, buyer_id, seller_id, total_amount, commission_amount, seller_payout, payment_status)
        VALUES (?, ?, ?, ?, ?, ?, 'unpaid')"
    );
    $stmt->bind_param(
        "iiiddd",
        $productId,
        $buyerId,
        $product['seller_id'],
        $totalAmount,
        $commissionAmount,
        $sellerPayout
    );
    $stmt->execute();

    $transactionId = $conn->insert_id;

    /* Create a payment record */
    $paymentReference = 'PAY-' . $transactionId . '-' . bin2hex(random_bytes(5));

    $stmt = $conn->prepare(
        "INSERT INTO payments
        (transaction_id, buyer_id, payment_reference, provider, amount, currency, status)
        VALUES (?, ?, ?, 'manual', ?, 'ETB', 'pending')"
    );
   

    // Remove the space in "iis d" before running:
    // It must be exactly: "iisd"
    $stmt->bind_param("iisd", $transactionId, $buyerId, $paymentReference, $totalAmount);
    $stmt->execute();

    $conn->commit();

} catch (Exception $e) {
    $conn->rollback();
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