<?php
require 'db.php';
if (!isset($_GET['id'])) { header("Location: index.php"); exit(); }
$product_id = intval($_GET['id']);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    if (isset($_POST['interaction'])) {
        $action = $_POST['interaction'] == 'like' ? 'like' : 'dislike';
        $conn->query("INSERT INTO likes (user_id, product_id, action) VALUES ($user_id, $product_id, '$action') ON DUPLICATE KEY UPDATE action='$action'");
    }
    if (isset($_POST['submit_comment'])) {
        $stmt = $conn->prepare("INSERT INTO comments (user_id, product_id, comment) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $product_id, $_POST['comment_text']);
        $stmt->execute();
    }
    header("Location: view_product.php?id=$product_id"); exit();
}

$stmt = $conn->prepare("SELECT p.*, u.name as seller_name, c.name as cat_name FROM products p JOIN users u ON p.seller_id = u.id JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
$stmt->bind_param("i", $product_id); $stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if(!$product) { header("Location: index.php"); exit(); }

$likes = $conn->query("SELECT COUNT(*) as c FROM likes WHERE product_id=$product_id AND action='like'")->fetch_assoc()['c'];
$dislikes = $conn->query("SELECT COUNT(*) as c FROM likes WHERE product_id=$product_id AND action='dislike'")->fetch_assoc()['c'];
$comments = $conn->query("SELECT c.*, u.name FROM comments c JOIN users u ON c.user_id = u.id WHERE c.product_id=$product_id ORDER BY c.created_at DESC");

require 'header.php';
?>
<div class="product-layout">
    <div>
        <?php $imgSrc = (!empty($product['image']) && file_exists('uploads/'.$product['image'])) ? 'uploads/'.$product['image'] : 'https://via.placeholder.com/600x400?text=No+Photo'; ?>
        <img src="<?php echo $imgSrc; ?>" class="product-main-img">
    </div>
    
    <div>
        <div class="badges">
            <span class="badge"><i class="fa-solid fa-tag"></i> <?php echo e($product['cat_name']); ?></span>
            <span class="badge"><i class="fa-solid fa-info-circle"></i> <?php echo e($product['item_condition']); ?></span>
            <span class="badge"><i class="fa-solid fa-truck"></i> <?php echo e($product['delivery_type']); ?></span>
        </div>
        
        <h1 style="margin: 15px 0; color: var(--dark);"><?php echo e($product['title']); ?></h1>
        <div class="price" style="font-size: 32px;">$<?php echo number_format($product['price'], 2); ?></div>
        
        <div style="margin: 20px 0; padding: 20px; background: var(--light); border-radius: 8px;">
            <h4 style="margin-bottom: 10px;"><i class="fa-solid fa-credit-card"></i> Payment Methods Accepted</h4>
            <div class="payment-methods">
                <?php 
                    $payments = explode(',', $product['payment_methods']);
                    foreach($payments as $pm) echo "<span class='pay-tag'>" . e(trim($pm)) . "</span>";
                ?>
            </div>
        </div>

        <p style="line-height: 1.8; color: var(--gray);"><?php echo nl2br(e($product['description'])); ?></p>
        <p style="margin-top:20px;"><strong>Seller:</strong> <i class="fa-solid fa-user"></i> <?php echo e($product['seller_name']); ?></p>

        <div style="display:flex; align-items:center; gap:15px; margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border);">
            <form method="POST" style="display:flex; gap:10px;">
                <button type="submit" name="interaction" value="like" class="btn btn-success"><i class="fa-solid fa-thumbs-up"></i> <?php echo $likes; ?></button>
                <button type="submit" name="interaction" value="dislike" class="btn btn-danger"><i class="fa-solid fa-thumbs-down"></i> <?php echo $dislikes; ?></button>
            </form>
            
            <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] != $product['seller_id'] && $product['status'] == 'Available'): ?>
                <a href="buy.php?id=<?php echo $product['id']; ?>" class="btn btn-primary" style="margin-left:auto; font-size: 18px;" onclick="return confirm('Complete purchase?');"><i class="fa-solid fa-cart-shopping"></i> Buy Now</a>
            <?php elseif($product['status'] == 'Sold'): ?>
                <span style="margin-left:auto; color:var(--danger); font-weight:bold; font-size:20px;">SOLD OUT</span>
            <?php endif; ?>
        </div>
    </div>
</div>

<div style="margin-top: 40px; background: white; padding: 30px; border-radius: var(--radius);">
    <h3><i class="fa-solid fa-comments"></i> Questions & Comments</h3>
    <?php if(isset($_SESSION['user_id'])): ?>
        <form method="POST" style="display:flex; gap:10px; margin: 20px 0;">
            <input type="text" name="comment_text" class="form-control" placeholder="Ask the seller a question..." required>
            <button type="submit" name="submit_comment" class="btn btn-primary">Post</button>
        </form>
    <?php else: ?>
        <p style="margin: 15px 0;"><a href="login.php">Login</a> to leave a comment.</p>
    <?php endif; ?>

    <div>
        <?php while($c = $comments->fetch_assoc()): ?>
            <div style="padding: 15px; background: var(--light); border-radius: 8px; margin-bottom: 10px;">
                <strong><?php echo e($c['name']); ?></strong> 
                <small style="color:var(--gray); float:right;"><?php echo date('M d, Y', strtotime($c['created_at'])); ?></small>
                <p style="margin-top:8px;"><?php echo e($c['comment']); ?></p>
            </div>
        <?php endwhile; ?>
    </div>
</div>

</div>
</body>
</html>