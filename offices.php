<?php
// offices.php
require_once 'layouts/header.php';
require_once 'classes/Office.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'employee')) {
    header("Location: dashboard.php");
    exit;
}

$officeObj = new Office($pdo);
$message = '';

// Handle Add Office
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    if ($officeObj->add($_POST['location_name'], $_POST['address'], $_POST['phone'])) {
        $message = "Офисът е добавен успешно!";
    } else {
        $message = "Грешка при добавяне.";
    }
}

// Handle Delete
if (isset($_GET['delete_id'])) {
    if ($officeObj->delete($_GET['delete_id'])) {
        $message = "Офисът е изтрит.";
    }
}

$offices = $officeObj->getAll();
?>

<h2>Управление на Офиси</h2>

<?php if ($message): ?>
    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div style="margin-bottom: 30px; border: 1px solid #ddd; padding: 20px; border-radius: 8px;">
    <h3>Добави нов офис</h3>
    <form method="POST" action="offices.php">
        <input type="hidden" name="action" value="add">
        <div style="display: flex; gap: 10px;">
            <input type="text" name="location_name" placeholder="Име на локация (напр. София Център)" class="form-control" required>
            <input type="text" name="address" placeholder="Адрес" class="form-control" required>
            <input type="text" name="phone" placeholder="Телефон" class="form-control" required>
            <button type="submit" class="btn btn-primary">Добави</button>
        </div>
    </form>
</div>

<h3>Списък офиси</h3>
<table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr style="background: #f0f0f0;">
            <th>ID</th>
            <th>Локация</th>
            <th>Адрес</th>
            <th>Телефон</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($offices as $office): ?>
            <tr>
                <td><?= $office['id'] ?></td>
                <td><?= htmlspecialchars($office['location_name']) ?></td>
                <td><?= htmlspecialchars($office['address']) ?></td>
                <td><?= htmlspecialchars($office['phone']) ?></td>
                <td>
                    <!-- Edit functionality could be added here -->
                    <a href="offices.php?delete_id=<?= $office['id'] ?>" class="btn" style="background: var(--danger-color); color: white; padding: 5px 10px; font-size: 0.8rem;" onclick="return confirm('Сигурни ли сте?')">Изтрий</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once 'layouts/footer.php'; ?>