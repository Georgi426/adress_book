<?php
// export.php
require_once 'config/db.php';
require_once 'classes/Contact.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    die("Access denied");
}

$format = $_GET['format'] ?? 'json'; // default to json, or csv
$contactObj = new Contact($pdo);

// Get all contacts with tags and custom fields would be ideal, but for basic export let's do main fields + tags string
$stmt = $pdo->prepare("
    SELECT c.*, 
    GROUP_CONCAT(t.name) as tags_list
    FROM contacts c
    LEFT JOIN contact_tags ct ON c.id = ct.contact_id
    LEFT JOIN tags t ON ct.tag_id = t.id
    WHERE c.user_id = ?
    GROUP BY c.id
");
$stmt->execute([$_SESSION['user_id']]);
$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($format === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="contacts_export.csv"');

    $output = fopen('php://output', 'w');
    // Header
    fputcsv($output, ['ID', 'First Name', 'Last Name', 'Company', 'Address', 'Landline', 'Mobile', 'Email', 'Fax', 'Note', 'Tags', 'Created At']);

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
            strip_tags($row['note']), // Strip HTML from notes for CSV
            $row['tags_list'],
            $row['created_at']
        ]);
    }
    fclose($output);
} else {
    // JSON
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="contacts_export.json"');
    echo json_encode($contacts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
