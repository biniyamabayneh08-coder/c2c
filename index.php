<?php
require 'db.php';
require 'header.php';

$search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';
$cat_id = isset($_GET['category']) ? intval($_GET['category']) : 0;

$sql = "SELECT p.*, c.name as cat_name FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.status = 'Available' AND (p.title LIKE ? OR p.description LIKE ?)";

if ($cat_id > 0) { 
    $sql .= " AND p.category_id = ?"; 
}
$sql .= " ORDER BY p.id DESC";
$stmt = $conn->prepare($sql);
if ($cat_id > 0) {
    $stmt->bind_param("ssi", $search, $search, $cat_id);
} else {
    $stmt->bind_param("ss", $search, $search);
}
$stmt->execute();
$products = $stmt->get_result();
$categories = $conn->query("SELECT * FROM categories");
?>

<?php if(isset($_GET['success'])): ?>
    <div style="background:var(--success); color:white; padding:15px; border-radius:8px; margin-bottom:20px;">
        🎉 Purchase completed successfully!
    </div>
<?php endif; ?>

<div class="filter-buttons">
<?php 
$categories = $conn->query("SELECT * FROM categories");
if ($categories && $categories->num_rows > 0) {
    while ($cat = $categories->fetch_assoc()) {
        $activeClass = ($cat_id == $cat['id']) ? 'active' : '';
        echo '<a href="index.php?category=' . $cat['id'] . '" class="btn ' . $activeClass . '">' . htmlspecialchars($cat['name']) . '</a>';
    }
}
?>
<div class="grid">
    <?php if($products->num_rows == 0): ?>
        <h3>No items found matching your search.</h3>
    <?php endif; ?>
    
    <?php while($item = $products->fetch_assoc()): ?>
        <div class="card">
            <?php $img = (!empty($item['image']) && file_exists('uploads/'.$item['image'])) ? 'uploads/'.$item['image'] : 'https://via.placeholder.com/400x300?text=No+Photo'; ?>
            <img src="<?php echo $img; ?>" class="card-img" alt="Item">
            
            <div class="card-body">
                <div class="badges">
                    <span class="badge"><i class="fa-solid fa-tag"></i> <?php echo e($item['cat_name']); ?></span>
                    <span class="badge"><?php echo e($item['item_condition']); ?></span>
                </div>
                <h3 style="font-size: 18px;"><?php echo e($item['title']); ?></h3>
                <div class="price">$<?php echo number_format($item['price'], 2); ?></div>
                
                <div class="card-footer">
                    <small style="color:var(--gray)"><i class="fa-solid fa-truck"></i> <?php echo e($item['delivery_type']); ?></small>
                    <a href="view_product.php?id=<?php echo $item['id']; ?>" class="btn btn-primary" style="padding: 6px 12px; font-size: 14px;">View Details</a>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
</div>

</div>
</body>
</html>