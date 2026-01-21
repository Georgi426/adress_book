<?php
// update_admin.php
require_once 'config/db.php';

$username = 'server_admin_company';
$newPassword = 'security_admin_2026';
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
$email = 'admin@logistics.com';

// Check if user exists, if not create, if yes update
// We check for either the OLD username or the NEW username to catch both cases
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR username = 'server_admin'");
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user) {
    // Update existing user
    $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?");
    if ($stmt->execute([$username, $hashedPassword, $user['id']])) {
        echo "Admin updated to '$username' with new password.<br>";

        // Log to admins.txt
        $logEntry = sprintf(
            "[%s] Updated Admin: %s | Role: admin | Email: %s | Password: %s\n",
            date('Y-m-d H:i:s'),
            $username,
            $email,
            $newPassword
        );

        file_put_contents('admins.txt', $logEntry, FILE_APPEND);
        echo "Admin credentials saved to admins.txt";
    } else {
        echo "Update failed.<br>";
    }
} else {
    // Create new user if neither exists
    $stmt = $pdo->prepare("INSERT INTO users (username, password, first_name, last_name, email, role) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$username, $hashedPassword, 'Server', 'Admin', $email, 'admin'])) {
        echo "Admin '$username' created.<br>";

        // Log to admins.txt
        $logEntry = sprintf(
            "[%s] Updated Admin: %s | Role: admin | Email: %s | Password: %s\n",
            date('Y-m-d H:i:s'),
            $username,
            $email,
            $newPassword
        );
        file_put_contents('admins.txt', $logEntry, FILE_APPEND);
        echo "Admin credentials saved to admins.txt";
    } else {
        echo "Creation failed.<br>";
    }
}
