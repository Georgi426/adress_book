<?php
// clients.php
require_once 'layouts/header.php';
require_once 'classes/User.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'employee')) {
    header("Location: dashboard.php");
    exit;
}

$userObj = new User($pdo);
$message = '';

if (isset($_GET['delete_id'])) {
    if ($userObj->delete($_GET['delete_id'])) {
        $message = "Клиентът е изтрит.";
    }
}

$clients = $userObj->getAllByRole('client');
?>

<h2>Списък Клиенти</h2>

<?php if ($message): ?>
    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr style="background: #f0f0f0;">
            <th>ID</th>
            <th>Потребител</th>
            <th>Име</th>
            <th>Email</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($clients as $client): ?>
            <tr>
                <td><?= $client['id'] ?></td>
                <td><?= htmlspecialchars($client['username']) ?></td>
                <td><?= htmlspecialchars($client['first_name'] . ' ' . $client['last_name']) ?></td>
                <td><?= htmlspecialchars($client['email']) ?></td>
                <td>
                    <a href="clients.php?delete_id=<?= $client['id'] ?>" class="btn" style="background: var(--danger-color); color: white; padding: 5px 10px;" onclick="return confirm('Сигурни ли сте?')">Изтрий</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once 'layouts/footer.php'; ?>