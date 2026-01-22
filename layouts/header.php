<?php
// layouts/header.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';
?>
<!DOCTYPE html>
<html lang="bg">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Логистична Компания</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <header>
        <div class="container">
            <div class="logo">
                <a href="index.php"><i class="fas fa-address-book"></i> Address Book</a>
            </div>
            <div class="menu-toggle" onclick="toggleMenu()">
                <i class="fas fa-bars"></i>
            </div>
            <nav id="nav-menu">
                <ul>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="dashboard.php">Контакти</a></li>
                        <li><a href="tags.php">Етикети</a></li>
                        <li><a href="custom_fields.php">Полета</a></li>
                        <li><a href="reports.php">Справки</a></li>
                        <li><a href="profile.php">Профил</a></li>
                        <li><a href="logout.php">Изход (<?= htmlspecialchars($_SESSION['username']) ?>)</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Вход</a></li>
                        <li><a href="register.php">Регистрация</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <main class="container">
        <!-- Main content start -->

        <script>
            function toggleMenu() {
                var nav = document.getElementById('nav-menu');
                nav.classList.toggle('active');
            }
        </script>