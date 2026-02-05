<?php

$message = '';
$error = '';

// Проверка дали формата е подадена
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Вземане на данните за връзка (или използване на стойности по подразбиране)
    $host = $_POST['host'] ?? '127.0.0.1';
    $user = $_POST['user'] ?? 'root';
    $pass = $_POST['pass'] ?? '';
    $db_name = $_POST['db_name'] ?? 'address_book_db';

    try {
        // 1. Свързване към MySQL сървъра БЕЗ да се избира конкретна база данни
        //    (за да можем да я създадем, ако липсва)
        $pdo = new PDO("mysql:host=$host", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 2. Създаване на базата данни (ако не съществува)
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");

        // 3. Избиране на новосъздадената база данни за работа
        $pdo->exec("USE `$db_name`");

        // 4. Импортиране на структурата от SQL файла (database.sql)
        $sql_file = __DIR__ . '/database.sql';
        if (!file_exists($sql_file)) {
            throw new Exception("Файлът database.sql липсва!");
        }

        // Четене на SQL файла ред по ред
        $lines = file($sql_file);
        $delimiter = ';'; // Стандартен разделител на заявките
        $query = '';

        foreach ($lines as $line) {
            $trimLine = trim($line);

            // Пропускане на празните редове и коментарите
            if (empty($trimLine) || strpos($trimLine, '--') === 0 || strpos($trimLine, '/*') === 0) {
                continue;
            }

            // Обработка на DELIMITER командата (често се ползва при Trigger-и или Store Procedures)
            if (preg_match('/^DELIMITER\s+(\S+)/i', $trimLine, $matches)) {
                $delimiter = $matches[1];
                continue;
            }

            // Долепяне на текущия ред към заявката
            $query .= $line;

            // Ако заявката завършва с текущия разделител, я изпълняваме
            if (substr(trim($query), -strlen($delimiter)) === $delimiter) {
                // Премахваме разделителя от края
                $statement = substr(trim($query), 0, -strlen($delimiter));

                if (!empty(trim($statement))) {
                    $pdo->exec($statement); // Изпълнение на SQL заявката
                }
                $query = ''; // Зачистване за следващата заявка
            }
        }

        // 5. Обновяване на конфигурационния файл `config/db.php` с новите данни
        // Тъй като install.php е в папка setup/, трябва да запишем в ../config/db.php
        $config_content = "<?php
// settings updated by install.php
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
        // Запис на новия конфиг файл
        file_put_contents(__DIR__ . '/../config/db.php', $config_content);

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Address Book Installation</title>
    <!-- Зареждане на основния CSS файл -->
    <link rel="stylesheet" href="../assets/css/style.css?v=<?= time() ?>">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <header>
        <div class="container">
            <div class="logo">
                <i class="fas fa-tools"></i> Инсталация на Address Book
            </div>
        </div>
    </header>

    <main class="container">
        <div class="auth-container" style="max-width: 600px;">
            <h2 class="text-center" style="margin-bottom: 30px;">Настройка на База Данни</h2>

            <?php if ($message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($message) ?>
                    <p style="margin-top: 15px; text-align: center;">
                        <a href="../index.php" class="btn btn-success">Към приложението <i class="fas fa-arrow-right"></i></a>
                    </p>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if (!$message): ?>
                <form method="POST">
                    <div class="form-group">
                        <label><i class="fas fa-server"></i> MySQL Host</label>
                        <input type="text" name="host" class="form-control" value="127.0.0.1" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> MySQL User</label>
                        <input type="text" name="user" class="form-control" value="root" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-key"></i> MySQL Password</label>
                        <input type="password" name="pass" class="form-control" placeholder="Парола за базата (ако има)">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-database"></i> Database Name</label>
                        <input type="text" name="db_name" class="form-control" value="address_book_db" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-cogs"></i> Инсталирай
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?= date('Y') ?> Address Book. Георги Илиев и Симеон Чобарски</p>
        </div>
    </footer>

</body>

</html>