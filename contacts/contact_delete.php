<?php
require_once '../config/db.php';
require_once '../classes/Contact.php';

session_start();

// Проверка за права и налично ID
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$contactObj = new Contact($pdo);
// Извикване на метод за изтриване (той прави проверка дали контактът принадлежи на потребителя)
$contactObj->delete($_GET['id'], $_SESSION['user_id']);

// Връщане към списъка
header("Location: dashboard.php");
exit;
