<?php

require_once '../layouts/header.php';
require_once '../classes/User.php';

if (isset($_SESSION['user_id'])) {
    header("Location: ../contacts/dashboard.php");
    exit;
}

$error = '';

// Обработка на формата при изпращане (POST заявка)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $userObj = new User($pdo);
    $user = $userObj->login($username, $password);

    if ($user) {
        // Успешен вход -> запазване на данни в сесията
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];

        // Пренасочване към основната страница
        header("Location: ../contacts/dashboard.php");
        exit;
    } else {
        // Неуспешен вход -> показване на грешка
        $error = "Невалидно потребителско име или парола.";
    }
}
?>

<div class="auth-container">
    <h2 style="text-align:center;">Вход</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="form-group">
            <label>Потребителско име</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Парола</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Вход</button>
    </form>
    <p style="text-align:center; margin-top:10px;">
        Нямате акаунт? <a href="register.php" style="color:var(--primary-color)">Регистрация</a>
    </p>
</div>

<?php require_once '../layouts/footer.php'; ?>