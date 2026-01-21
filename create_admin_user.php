<?php
// create_admin_user.php
require_once 'config/db.php';
require_once 'classes/User.php';

$userObj = new User($pdo);

// Desired credentials
$username = 'server_admin';
$password = 'admin_secure_pass';
$email = 'admin@logistics.com';

// Check if exists
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$username]);
if ($stmt->fetch()) {
    echo "<h1>Потребител '$username' вече съществува!</h1>";
} else {
    // Register
    if ($userObj->register($username, $password, 'Server', 'Admin', $email, 'admin')) {
        echo "<h1>Администраторският акаунт е създаден успешно!</h1>";
        echo "<ul>";
        echo "<li>Потребител: <strong>$username</strong></li>";
        echo "<li>Парола: <strong>$password</strong></li>";
        echo "</ul>";
        echo "<p>Тези данни са записани и в <code>users.txt</code></p>";
        echo "<a href='login.php'>Към Вход</a>";
    } else {
        echo "<h1>Грешка при създаване!</h1>";
    }
}
