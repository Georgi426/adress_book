<?php
// install.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = '127.0.0.1';
$user = 'root';
$pass = 'Kartanz2004!'; // Adjust if needed based on previous steps
$db   = 'logistics_company';

echo "<h1>Инсталация на база данни</h1>";

try {
    // 1. Connect without Database to create it
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<li>Свързване към MySQL успешно.</li>";

    // 2. Create Database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    echo "<li>База данни `$db` е създадена (или вече съществува).</li>";

    // 3. Select Database
    $pdo->exec("USE `$db`");

    // 4. Import SQL
    $sql_file = __DIR__ . '/database.sql';
    if (!file_exists($sql_file)) {
        die("<li style='color:red'>Файлът database.sql липсва!</li>");
    }

    $sql = file_get_contents($sql_file);

    // Split by semicolons for multiple statements if driver doesn't support batch
    // Simple approach: PDO::exec often supports multiple queries
    $pdo->exec($sql);

    echo "<li style='color:green'>Таблиците са импортирани успешно!</li>";
    echo "<h3><a href='index.php'>Към Приложението</a></h3>";
} catch (PDOException $e) {
    echo "<h2 style='color:red'>Грешка: " . $e->getMessage() . "</h2>";
    echo "<p>Уверете се, че паролата в <code>install.php</code> е вярна.</p>";
}
