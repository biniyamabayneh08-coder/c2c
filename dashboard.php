<?php
require 'db.php';
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit(); }
$user_id = $_SESSION['user_id'];

$my_listings = $conn->query("SELECT * FROM products WHERE seller_id = $user_id ORDER BY id DESC");
$my_purchases = $conn->query("SELECT t.*, p.title FROM transactions t JOIN products p ON t.product_id = p.id WHERE t.buyer_id = $user_id ORDER BY t.id DESC");
$platform_commissions = $conn->query("SELECT t.*, p.title FROM transactions t JOIN products p ON t.product_id = p.id ORDER BY t.id DESC");

require 'header.php';
?>
<h2><i class="fa-solid fa-user-gear"></i> User Dashboard</h2>

<h3 style="margin-top:30px;">My Listings</h3>
<table class="table">
    <tr><th>Title</th><th>Price</th><th>Condition</th><th>Status</th></tr>
    <?php while($row = $my_listings->fetch_assoc()): ?>
        <tr>
            <td><?php echo e($row['title']); ?></td>
            <td>$<?php echo number_format($row['price'], 2); ?></td>
            <td><?php echo e($row['item_condition']); ?></td>
            <td><span class="badge"><?php echo e($row['status']); ?></span></td>
        </tr>
    <?php endwhile; ?>
</table>

<h3 style="margin-top:30px;">My Purchase History</h3>
<table class="table">
    <tr><th>Item</th><th>Total Paid</th><th>Seller Payout</th><th>Date</th></tr>
    <?php while($row = $my_purchases->fetch_assoc()): ?>
        <tr>
            <td><?php echo e($row['title']); ?></td>
            <td>$<?php echo number_format($row['total_amount'], 2); ?></td>
            <td>$<?php echo number_format($row['seller_payout'], 2); ?></td>
            <td><?php echo $row['transaction_date']; ?></td>
        </tr>
    <?php endwhile; ?>
</table>

<h3 style="margin-top:30px;"><i class="fa-solid fa-coins"></i> Website Owner Commission Ledger</h3>
<table class="table">
    <tr><th>Product</th><th>Sale Amount</th><th>Website Commission (10%)</th><th>Date</th></tr>
    <?php while($row = $platform_commissions->fetch_assoc()): ?>
        <tr>
            <td><?php echo e($row['title']); ?></td>
            <td>$<?php echo number_format($row['total_amount'], 2); ?></td>
            <td style="color:var(--success); font-weight:bold;">$<?php echo number_format($row['commission_amount'], 2); ?></td>
            <td><?php echo $row['transaction_date']; ?></td>
        </tr>
    <?php endwhile; ?>
</table>

</div>
</body>
</html>