<?php

require_once 'config/db.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Проверка за активен потребител
if (isset($_SESSION['user_id'])) {
    // Влезлият потребител отива на Dashboard
    header("Location: contacts/dashboard.php");
} else {
    // Гост потребителя отива на Вход
    header("Location: auth/login.php");
}
exit;
