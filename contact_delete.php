<?php
// contact_delete.php
require_once 'config/db.php';
require_once 'classes/Contact.php';

session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$contactObj = new Contact($pdo);
$contactObj->delete($_GET['id'], $_SESSION['user_id']);

header("Location: dashboard.php");
exit;
