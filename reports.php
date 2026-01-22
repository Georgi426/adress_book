<?php
// reports.php
require_once 'layouts/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$report_type = $_GET['type'] ?? 'all';
$results = [];
$title = "Справки";

switch ($report_type) {
    case 'tag_stats':
        $title = "Най-често срещани етикети";
        // 7.2. Записите с най-често срещани етикети (обобщени данни)
        $mt = $pdo->prepare("
            SELECT t.name, t.color, COUNT(ct.contact_id) as count 
            FROM tags t
            JOIN contact_tags ct ON t.id = ct.tag_id
            WHERE t.user_id = ?
            GROUP BY t.id
            ORDER BY count DESC
        ");
        $mt->execute([$_SESSION['user_id']]);
        $results = $mt->fetchAll(PDO::FETCH_ASSOC);
        break;

    case 'same_name_diff_last':
        $title = "Еднакви имена, различни фамилии";
        // 7.3. Всички записи с еднакви имена и различни фамилии
        // Find first_names that appear > 1 time
        $stmt = $pdo->prepare("
            SELECT * FROM contacts c1
            WHERE c1.user_id = ? 
            AND c1.first_name IN (
                SELECT first_name FROM contacts WHERE user_id = ? GROUP BY first_name HAVING COUNT(*) > 1
            )
            ORDER BY c1.first_name, c1.last_name
        ");
        $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        break;

    case 'same_last_diff_name':
        $title = "Еднакви фамилии, различни имена";
        // 7.4. Всички записи с еднакви фамилии и различни имена
        $stmt = $pdo->prepare("
            SELECT * FROM contacts c1
            WHERE c1.user_id = ? 
            AND c1.last_name IN (
                SELECT last_name FROM contacts WHERE user_id = ? GROUP BY last_name HAVING COUNT(*) > 1
            )
            ORDER BY c1.last_name, c1.first_name
        ");
        $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        break;

    case 'all':
    default:
        $title = "Всички записи";
        // 7.1. Всички запазени в системата записи;
        $stmt = $pdo->prepare("SELECT * FROM contacts WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$_SESSION['user_id']]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        break;
}

?>

<h2><?= htmlspecialchars($title) ?></h2>

<div class="reports-navigation">
    <a href="reports.php?type=all" class="btn <?= $report_type == 'all' ? 'btn-primary' : 'btn-secondary' ?>">Всички</a>
    <a href="reports.php?type=tag_stats" class="btn <?= $report_type == 'tag_stats' ? 'btn-primary' : 'btn-secondary' ?>">Статистика Етикети</a>
    <a href="reports.php?type=same_name_diff_last" class="btn <?= $report_type == 'same_name_diff_last' ? 'btn-primary' : 'btn-secondary' ?>">Дублиращи Имена</a>
    <a href="reports.php?type=same_last_diff_name" class="btn <?= $report_type == 'same_last_diff_name' ? 'btn-primary' : 'btn-secondary' ?>">Дублиращи Фамилии</a>
    <a href="export.php" class="btn btn-info ms-auto" target="_blank">Експорт (CSV/JSON)</a>
</div>

<?php if ($report_type === 'tag_stats'): ?>
    <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #f0f0f0;">
                <th>Етикет</th>
                <th>Брой записи</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($results as $row): ?>
                <tr>
                    <td>
                        <span style="display:inline-block; width:15px; height:15px; background:<?= $row['color'] ?>; margin-right:5px;"></span>
                        <?= htmlspecialchars($row['name']) ?>
                    </td>
                    <td><?= $row['count'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <!-- List of Contacts -->
    <?php if (empty($results)): ?>
        <p>Няма намерени резултати.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f0f0f0;">
                        <th>Име</th>
                        <th>Фамилия</th>
                        <th>Фирма</th>
                        <th>Email</th>
                        <th>Телефон</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $contact): ?>
                        <tr>
                            <td><?= htmlspecialchars($contact['first_name']) ?></td>
                            <td><?= htmlspecialchars($contact['last_name']) ?></td>
                            <td><?= htmlspecialchars($contact['company_name']) ?></td>
                            <td><?= htmlspecialchars($contact['email']) ?></td>
                            <td><?= htmlspecialchars($contact['phone_mobile']) ?></td>
                            <td>
                                <a href="contact_edit.php?id=<?= $contact['id'] ?>">Преглед/Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php require_once 'layouts/footer.php'; ?>