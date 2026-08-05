<?php
require 'db.php';
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit(); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $seller_id = $_SESSION['user_id'];
    $cat_id    = $_POST['category_id'];
    $title     = $_POST['title'];
    $desc      = $_POST['description'];
    $price     = $_POST['price'];
    $condition = $_POST['item_condition'];
    $delivery  = $_POST['delivery_type'];
    $payments  = isset($_POST['payments']) ? implode(', ', $_POST['payments']) : 'Cash';

    $imageName = ''; 
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0)
     {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $imageName = uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/' . $imageName);
    }

    $stmt = $conn->prepare("INSERT INTO products (seller_id, category_id, title, description, price, item_condition, delivery_type, payment_methods, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissdssss", $seller_id, $cat_id, $title, $desc, $price, $condition, $delivery, $payments, $imageName);
    
    if ($stmt->execute()) { header('Location: index.php'); exit(); }
}

$categories = $conn->query("SELECT * FROM categories");
require 'header.php';
?>
<div class="card-form">
    <h2 style="margin-bottom: 20px;"><i class="fa-solid fa-box-open"></i> Post an Item</h2>
    <form method="POST" enctype="multipart/form-data">
        
        <div class="form-group">
            <label>Category</label>
            <select name="category_id" class="form-control" required>
                <?php while($c = $categories->fetch_assoc()): ?>
                    <option value="<?php echo $c['id']; ?>"><?php echo e($c['name']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group"><label>Title</label><input type="text" name="title" class="form-control" required></div>
        <div class="form-group"><label>Product Photo</label><input type="file" name="image" accept="image/*" class="form-control" required></div>
        <div class="form-group"><label>Description</label><textarea name="description" rows="4" class="form-control" required></textarea></div>
        <div class="form-group"><label>Price ($)</label><input type="number" step="0.01" name="price" class="form-control" required></div>
        
        <div style="display:flex; gap:20px;">
            <div class="form-group" style="flex:1;">
                <label>Condition</label>
                <select name="item_condition" class="form-control">
                    <option>New</option><option>Used</option><option>Not Working</option><option>Spare Parts</option>
                </select>
            </div>
            <div class="form-group" style="flex:1;">
                <label>Delivery Option</label>
                <select name="delivery_type" class="form-control">
                    <option>Postal Service</option><option>Home Delivery</option><option>Face to Face</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Accepted Payment Methods</label>
            <div style="display:flex; gap:15px; flex-wrap:wrap; background:var(--light); padding:15px; border-radius:8px; border:1px solid var(--border);">
                <label><input type="checkbox" name="payments[]" value="Cash"> Cash</label>
                <label><input type="checkbox" name="payments[]" value="Bank Transfer"> Bank Transfer</label>
                <label><input type="checkbox" name="payments[]" value="PayPal"> PayPal</label>
                <label><input type="checkbox" name="payments[]" value="Mobile Money"> Mobile Money</label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:15px; font-size:16px;">Publish Listing</button>
    </form>
</div>

</div>
</body>
</html>