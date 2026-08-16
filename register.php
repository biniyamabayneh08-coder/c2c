<?php
require 'db.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $conn->prepare("INSERT INTO users (name, username, email, password_hash) VALUES (?, ?, ?, ?)");
    $hash = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $stmt->bind_param("ssss", $_POST['name'], $_POST['name'], $_POST['email'], $hash);
    if ($stmt->execute()) { header('Location: login.php'); exit(); }
}
require 'header.php';
?>
<div class="card-form">
    <h2 style="text-align:center; margin-bottom:20px;">Create Account</h2>
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