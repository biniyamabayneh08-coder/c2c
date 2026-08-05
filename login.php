<?php
require 'db.php';
$error = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $_POST['email']); $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    if ($user && password_verify($_POST['password'], $user['password'])) {
        $_SESSION['user_id'] = $user['id']; 
        $_SESSION['user_name'] = $user['name'];
        header('Location: index.php'); exit();
    } else {
        $error = "Invalid email or password.";
    }
}
require 'header.php';
?>
<div class="card-form">
    <h2 style="text-align:center; margin-bottom:20px;">Welcome Back</h2>
    <?php if($error): ?><p style="color:var(--danger); margin-bottom:15px;"><?php echo $error; ?></p><?php endif; ?>
    <form method="POST">
        <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" required></div>
        <div class="form-group"><label>Password</label><input type="password" name="password" class="form-control" required></div>
        <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">Login</button>
    </form>
</div>

</div>
</body>
</html>
