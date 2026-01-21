<?php
// employees.php
require_once 'layouts/header.php';
require_once 'classes/User.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

$userObj = new User($pdo);
$message = '';

// Handle Delete
if (isset($_GET['delete_id'])) {
    if ($userObj->delete($_GET['delete_id'])) {
        $message = "Служителят е изтрит.";
    } else {
        $message = "Грешка при изтриване.";
    }
}

// Handle Add (Simple registration within admin panel)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);

    if ($userObj->register($username, $password, $first_name, $last_name, $email, 'employee')) {
        $message = "Служителят е регистриран успешно.";
    } else {
        $message = "Грешка при регистрация (може би потребителското име е заето).";
    }
}

$employees = $userObj->getAllByRole('employee');
?>

<h2>Управление на Служители</h2>

<?php if ($message): ?>
    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div style="margin-bottom: 30px; border: 1px solid #ddd; padding: 20px; border-radius: 8px;">
    <h3>Добави нов служител</h3>
    <form method="POST" action="employees.php">
        <input type="hidden" name="action" value="add">
        <div class="row" style="display:flex; flex-wrap:wrap; gap:10px;">
            <div class="col"><input type="text" name="username" placeholder="Потребителско име" class="form-control" required></div>
            <div class="col"><input type="password" name="password" placeholder="Парола" class="form-control" required></div>
            <div class="col"><input type="text" name="first_name" placeholder="Име" class="form-control" required></div>
            <div class="col"><input type="text" name="last_name" placeholder="Фамилия" class="form-control" required></div>
            <div class="col"><input type="email" name="email" placeholder="Email" class="form-control"></div>
            <div class="col"><button type="submit" class="btn btn-primary">Създай</button></div>
        </div>
    </form>
</div>

<h3>Списък служители</h3>
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
        <?php foreach ($employees as $emp): ?>
            <tr>
                <td><?= $emp['id'] ?></td>
                <td><?= htmlspecialchars($emp['username']) ?></td>
                <td><?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?></td>
                <td><?= htmlspecialchars($emp['email']) ?></td>
                <td>
                    <a href="employees.php?delete_id=<?= $emp['id'] ?>" class="btn" style="background: var(--danger-color); color: white; padding: 5px 10px;" onclick="return confirm('Сигурни ли сте?')">Изтрий</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once 'layouts/footer.php'; ?>