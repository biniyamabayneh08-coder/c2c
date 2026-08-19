<?php
// Ensure session is active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'db.php';
$error = "";
$email_value = "";

// Generate CSRF token if it doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Security validation failed. Please try again.";
    } else {
        $email_value = $_POST['email'] ?? '';
        
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email_value); 
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        
        if ($user && password_verify($_POST['password'], $user['password_hash'])) {
            // Prevent session fixation attacks
            session_regenerate_id(true);
            
            $_SESSION['user_id'] = $user['id']; 
            $_SESSION['user_name'] = $user['name'];
            
            // Redirect safely
            header('Location: index.php'); 
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    }
}
require 'header.php';
?>
<div class="card-form">
    <h2 style="text-align:center; margin-bottom:20px;">Welcome Back</h2>
    
    <?php if($error): ?>
        <p style="color:var(--danger); margin-bottom:15px;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    
    <form method="POST">
        <!-- Hidden CSRF Token -->
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email_value); ?>" required>
        </div>
        
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <!-- Future-ready Remember Me Checkbox -->
        <div class="form-group" style="display: flex; align-items: center; gap: 8px; margin-bottom: 15px;">
            <input type="checkbox" name="remember" id="remember">
            <label for="remember" style="margin-bottom: 0; font-weight: normal; cursor: pointer;">Remember me</label>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">Login</button>
    </form>
</div>

</div>
</body>
</html>