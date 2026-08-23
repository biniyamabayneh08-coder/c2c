<?php
require 'db.php';
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        $stmt = $conn->prepare("INSERT INTO users (name, username, email, password_hash) VALUES (?, ?, ?, ?)");
        $hash = password_hash($password, PASSWORD_BCRYPT);
        
        if ($stmt->execute([$name, $name, $email, $hash])) { 
            header('Location: login.php'); 
            exit(); 
        }
    } catch (PDOException $e) {
        // Catch duplicate key error code (23505) gracefully
        if ($e->getCode() == '23505') {
            $error = "This email is already registered. Please use a different email or log in.";
        } else {
            $error = "Registration failed: " . $e->getMessage();
        }
    }
}

require 'header.php';
?>
<div class="card-form">
    <h2 style="text-align:center; margin-bottom:20px;">Create Account</h2>
    
    <?php if($error): ?>
        <p style="color:var(--danger, #dc3545); margin-bottom:15px; text-align:center;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group"><label>Name</label><input type="text" name="name" class="form-control" required></div>
        <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" required></div>
        <div class="form-group"><label>Password</label><input type="password" name="password" class="form-control" required></div>
        <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">Register</button>
    </form>
</div>

</div>
</body>
</html>