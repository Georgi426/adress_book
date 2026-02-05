<?php
require_once '../layouts/header.php';
require_once '../classes/User.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userObj = new User($pdo);
$message = '';
$error = '';

// Извличане на текущите данни на потребителя
$user = $userObj->getUserById($_SESSION['user_id']);

// Обработка на формата за редакция
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    // Валидация на задължителните полета
    if (empty($first_name) || empty($last_name)) {
        $error = "Името и Фамилията са задължителни.";
    } else {
        // Опит за обновяване на данните в базата
        if ($userObj->update($_SESSION['user_id'], $first_name, $last_name, $email)) {
            $message = "Профилът е обновен успешно.";
            // Обновяване на информацията в сесията, за да се отрази веднага в хедъра
            $_SESSION['first_name'] = $first_name;
            $_SESSION['last_name'] = $last_name;
            // Презареждане на данните за попълване на формата
            $user = $userObj->getUserById($_SESSION['user_id']);
        } else {
            $error = "Възникна грешка при обновяването.";
        }
    }
}
?>

<h2>Редактиране на Профил</h2>

<?php if ($message): ?>
    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" action="profile.php" style="max-width: 500px;">
    <div class="form-group">
        <label>Потребителско име</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled style="background: #eee;">
    </div>

    <div class="form-group">
        <label>Име</label>
        <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($user['first_name']) ?>" required>
    </div>

    <div class="form-group">
        <label>Фамилия</label>
        <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($user['last_name']) ?>" required>
    </div>

    <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>">
    </div>

    <button type="submit" class="btn btn-primary">Запази Промените</button>
</form>

<?php require_once '../layouts/footer.php'; ?>