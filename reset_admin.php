<?php
// reset_admin.php
require_once 'config/db.php';

$new_password = 'admin';
$username = 'admin';

try {
    $hash = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = ?");
    $result = $stmt->execute([$hash, $username]);

    if ($result && $stmt->rowCount() > 0) {
        echo "<h1>Success!</h1>";
        echo "<p>Password for user <strong>$username</strong> has been reset to: <strong>$new_password</strong></p>";
        echo "<p><a href='login.php'>Go to Login</a></p>";
    } else {
        echo "<h1>Error</h1>";
        echo "<p>User '$username' not found or password is already set to 'admin'.</p>";
        // Try creating if not exists
        echo "<p>Attempting to create admin user...</p>";

        $stmt_create = $pdo->prepare("INSERT INTO users (username, password, role, first_name, last_name, email) VALUES (?, ?, 'admin', 'System', 'Admin', 'admin@addressbook.com')");
        if ($stmt_create->execute([$username, $hash])) {
            echo "<h1>Success!</h1>";
            echo "<p>User <strong>$username</strong> created with password: <strong>$new_password</strong></p>";
            echo "<p><a href='login.php'>Go to Login</a></p>";
        } else {
            echo "<p>Failed to create user.</p>";
        }
    }
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage();
}
