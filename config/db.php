<?php

// Настройки за връзка с базата данни
$host = '127.0.0.1';
$db   = 'logistics_company';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

// DSN (Data Source Name) низ за връзка
$dsn = "mysql:host=$host;port=3310;dbname=$db;charset=$charset";

// Опции за PDO връзката
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Хвърляне на изключения при грешки
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,      // Връщане на резултатите като асоциативен масив
    PDO::ATTR_EMULATE_PREPARES   => false,                 // Използване на native prepared statements
];

try {
    // Създаване на нова PDO инстанция
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Хвърляне на грешка при неуспешна връзка
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
