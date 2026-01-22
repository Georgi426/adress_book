<?php
// install.php
// Installation script for Address Book App

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = $_POST['host'] ?? '127.0.0.1';
    $user = $_POST['user'] ?? 'root';
    $pass = $_POST['pass'] ?? '';
    $db_name = $_POST['db_name'] ?? 'address_book_db';

    try {
        // 1. Connect without Database to create it
        $pdo = new PDO("mysql:host=$host", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 2. Create Database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");

        // 3. Select Database
        $pdo->exec("USE `$db_name`");

        // 4. Import SQL
        $sql_file = __DIR__ . '/database.sql';
        if (!file_exists($sql_file)) {
            throw new Exception("Файлът database.sql липсва!");
        }

        $sql = file_get_contents($sql_file);

        // Execute SQL import
        $pdo->exec($sql);

        // 5. Update config/db.php with new credentials
        $config_content = "<?php
\$host = '$host';
\$db   = '$db_name';
\$user = '$user';
\$pass = '$pass';
\$charset = 'utf8mb4';

\$dsn = \"mysql:host=\$host;dbname=\$db;charset=\$charset\";
\$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    \$pdo = new PDO(\$dsn, \$user, \$pass, \$options);
} catch (\PDOException \$e) {
    throw new \PDOException(\$e->getMessage(), (int)\$e->getCode());
}
?>";
        file_put_contents(__DIR__ . '/config/db.php', $config_content);

        $message = "Инсталацията е успешна! Базата данни е създадена и конфигурирана.";
    } catch (Exception $e) {
        $error = "Грешка: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="bg">

<head>
    <meta charset="UTF-8">
    <title>Address Book Installation</title>
    <style>
        body {
            font-family: sans-serif;
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
            line-height: 1.6;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }

        button {
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }

        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
    </style>
</head>

<body>

    <h1>Инсталация на Address Book</h1>

    <?php if ($message): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($message) ?>
            <p><a href="index.php">Към приложението</a></p>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (!$message): ?>
        <form method="POST">
            <div class="form-group">
                <label>MySQL Host</label>
                <input type="text" name="host" value="127.0.0.1" required>
            </div>
            <div class="form-group">
                <label>MySQL User</label>
                <input type="text" name="user" value="root" required>
            </div>
            <div class="form-group">
                <label>MySQL Password</label>
                <input type="password" name="pass" placeholder="Your DB Password">
            </div>
            <div class="form-group">
                <label>Database Name</label>
                <input type="text" name="db_name" value="address_book_db" required>
            </div>
            <button type="submit">Инсталирай</button>
        </form>
    <?php endif; ?>

</body>

</html>