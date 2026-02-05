<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Включване на конфигурацията за базата данни.
// Използваме __DIR__ за да сме сигурни, че пътят е верен спрямо текущия файл.
require_once __DIR__ . '/../config/db.php';
?>
<!DOCTYPE html>
<html lang="bg">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Address Book</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=<?= time() ?>">

    <!-- FontAwesome: Библиотека за икони (използва се за бутоните и менютата) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <header>
        <div class="container">
            <div class="logo">
                <a href="../index.php"><i class="fas fa-address-book"></i> Address Book</a>
            </div>

            <!-- Бутон за мобилно меню -->
            <div class="menu-toggle" onclick="toggleMenu()">
                <i class="fas fa-bars"></i>
            </div>

            <!-- Навигационно меню -->
            <nav id="nav-menu">
                <ul>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- Меню за логнати потребители -->
                        <li><a href="../contacts/dashboard.php">Контакти</a></li>
                        <li><a href="../contacts/tags.php">Етикети</a></li>
                        <li><a href="../contacts/custom_fields.php">Полета</a></li>
                        <li><a href="../contacts/reports.php">Справки</a></li>
                        <li><a href="../auth/profile.php">Профил</a></li>
                        <li><a href="../auth/logout.php">Изход (<?= htmlspecialchars($_SESSION['username']) ?>)</a></li>
                    <?php else: ?>
                        <!-- Меню за гости (нелогнати) -->
                        <li><a href="../auth/login.php">Вход</a></li>
                        <li><a href="../auth/register.php">Регистрация</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <!-- Основен контейнер за съдържанието, което ще се зареди в отделните страници -->
    <main class="container">
        <!-- Скрипт за превключване на мобилното меню -->
        <script>
            function toggleMenu() {
                var nav = document.getElementById('nav-menu');
                nav.classList.toggle('active');
            }
        </script>