<?php
// index.php
require_once 'config/db.php'; // Ensure session is started via db.php or manually if it handles it. 
// Assuming header.php was handling session start, but we are removing header.php includes if we just redirect.
// Looking at other files, layouts/header.php likely starts the session.
// However, since we want to redirect BEFORE outputting HTML, we should just check session.
// If db.php starts session, good. If not, we start it.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
} else {
    header("Location: login.php");
}
exit;
