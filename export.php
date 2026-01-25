<?php

require_once 'config/db.php';
require_once 'classes/Contact.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    die("Access denied");
}

$format = $_GET['format'] ?? 'json'; // Формат по подразбиране: json (може и csv)
$contactObj = new Contact($pdo);

// Извличане на всички контакти с техните етикети (обединени като низ) за по-лесен експорт
$stmt = $pdo->prepare("
    SELECT c.*, 
    GROUP_CONCAT(t.name) as tags_list
    FROM contacts c
    LEFT JOIN contact_tags ct ON c.id = ct.contact_id
    LEFT JOIN tags t ON ct.tag_id = t.id
    WHERE c.user_id = :user_id
    GROUP BY c.id
");
$stmt->execute([':user_id' => $_SESSION['user_id']]);
$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($format === 'csv') {
    // Настройки за сваляне на CSV файл
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="contacts_export.csv"');

    // Отваряне на output stream за писане
    $output = fopen('php://output', 'w');

    // Записване на заглавния ред (Header) с имената на колоните
    fputcsv($output, ['ID', 'First Name', 'Last Name', 'Company', 'Address', 'Landline', 'Mobile', 'Email', 'Fax', 'Note', 'Tags', 'Created At']);

    // Записване на данните ред по ред
    foreach ($contacts as $row) {
        fputcsv($output, [
            $row['id'],
            $row['first_name'],
            $row['last_name'],
            $row['company_name'],
            $row['address'],
            $row['phone_landline'],
            $row['phone_mobile'],
            $row['email'],
            $row['fax'],
            strip_tags($row['note']), // Премахване на HTML таговете от бележките за да не чупят CSV-то
            $row['tags_list'],
            $row['created_at']
        ]);
    }
    fclose($output);
} else {
    // Експорт в JSON формат (по-подходящ за програмисти или пренос на данни)
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="contacts_export.json"');

    // Кодиране на масива в JSON с опции за четимост и поддръжка на кирилица
    echo json_encode($contacts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
