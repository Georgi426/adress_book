<?php
session_start();
session_unset();
// Унищожаване на сесията напълно
session_destroy();
// Пренасочване към началната страница
header("Location: index.php");
exit;
