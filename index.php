<?php
require 'db.php';
require 'header.php';

// 1. Sanitize & retrieve query parameters
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$search = '%' . $search_term . '%';
$cat_id = isset($_GET['category']) ? intval($_GET['category']) : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// 2. Dynamic sorting options
$order_clause = "p.id DESC";
if ($sort === 'price_asc') {
    $order_clause = "p.price ASC";
} elseif ($sort === 'price_desc') {
    $order_clause = "p.price DESC";
}

// 3. Prepare product SQL query
$sql = "SELECT p.*, c.name as cat_name FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.status = 'Available' AND (p.title LIKE ? OR p.description LIKE ?)";

if ($cat_id > 0) {
    $sql .= " AND p.category_id = ?";
}
$sql .= " ORDER BY " . $order_clause;

$stmt = $conn->prepare($sql);
if ($cat_id > 0) {
    $stmt->bind_param("ssi", $search, $search, $cat_id);
} else {
    $stmt->bind_param("ss", $search, $search);
}
$stmt->execute();
$products = $stmt->get_result();

// 4. Fetch categories once to prevent duplicate database queries
$categories_res = $conn->query("SELECT * FROM categories");
?>

<?php if(isset($_GET['success'])): ?>
    <div style="background:var(--success, #28a745); color:white; padding:15px; border-radius:8px; margin-bottom:20px;">
        🎉 Purchase completed successfully!
    </div>
<?php endif; ?>

<!-- FEATURE 1 & 2: Search Bar & Sorting Controls -->
<div class="search-sort-container" style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; justify-content: space-between;">
    <form method="GET" action="index.php" style="display: flex; gap: 8px; flex: 1; max-width: 500px;">
        <?php if ($cat_id > 0): ?>
            <input type="hidden" name="category" value="<?php echo $cat_id; ?>">
        <?php endif; ?>
        <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search_term); ?>" style="padding: 8px 12px; border-radius: 6px; border: 1px solid #ccc; width: 100%;">
        <button type="submit" class="btn btn-primary" style="padding: 8px 16px;">Search</button>
        <?php if (!empty($search_term)): ?>
            <a href="index.php<?php echo $cat_id ? '?category='.$cat_id : ''; ?>" class="btn" style="padding: 8px 12px; background: #eee;">Clear</a>
        <?php endif; ?>
    </form>

    <form method="GET" action="index.php" style="display: flex; align-items: center; gap: 8px;">
        <?php if ($cat_id > 0): ?><input type="hidden" name="category" value="<?php echo $cat_id; ?>"><?php endif; ?>
        <?php if (!empty($search_term)): ?><input type="hidden" name="search" value="<?php echo htmlspecialchars($search_term); ?>"><?php endif; ?>
        <label for="sort" style="font-size: 14px; color: var(--gray, #666);">Sort By:</label>
        <select name="sort" id="sort" onchange="this.form.submit()" style="padding: 8px; border-radius: 6px; border: 1px solid #ccc;">
            <option value="newest" <?php if ($sort === 'newest') echo 'selected'; ?>>Newest First</option>
            <option value="price_asc" <?php if ($sort === 'price_asc') echo 'selected'; ?>>Price: Low to High</option>
            <option value="price_desc" <?php if ($sort === 'price_desc') echo 'selected'; ?>>Price: High to Low</option>
        </select>
    </form>
</div>

<!-- FEATURE 3: Category Buttons with "All" Filter Option -->
<div class="filter-buttons" style="margin-bottom: 20px;">
    <a href="index.php<?php echo !empty($search_term) ? '?search='.urlencode($search_term) : ''; ?>" class="btn <?php echo ($cat_id == 0) ? 'active' : ''; ?>">All</a>
    <?php 
    if ($categories_res && $categories_res->num_rows > 0) {
        while ($cat = $categories_res->fetch_assoc()) {
            $activeClass = ($cat_id == $cat['id']) ? 'active' : '';
            $url = 'index.php?category=' . $cat['id'];
            if (!empty($search_term)) {
                $url .= '&search=' . urlencode($search_term);
            }
            if ($sort !== 'newest') {
                $url .= '&sort=' . urlencode($sort);
            }
            echo '<a href="' . $url . '" class="btn ' . $activeClass . '">' . htmlspecialchars($cat['name']) . '</a> ';
        }
    }
    ?>
</div>

<!-- Product Display Grid -->
<div class="grid">
    <?php if($products->num_rows == 0): ?>
        <h3>No items found matching your search.</h3>
    <?php else: ?>
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
    <?php endif; ?>
</div>

</body>
</html>