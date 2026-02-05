<?php

require_once '../layouts/header.php';
require_once '../classes/User.php';

$message = '';
$error = '';

// Обработка на POST заявка (изпращане на регистрационната форма)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Събиране на данните от формата
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);

    // Валидация: Проверка дали всички задължителни полета са попълнени
    if (empty($username) || empty($password) || empty($first_name) || empty($last_name)) {
        $error = "Моля попълнете всички задължителни полета.";
    } else {
        $userObj = new User($pdo);
        // Опит за регистрация (ролята по подразбиране е 'client')
        if ($userObj->register($username, $password, $first_name, $last_name, $email, 'client')) {
            // При успешна регистрация -> автоматичен вход
            $user = $userObj->login($username, $password);
            if ($user) {
                // Инициализиране на сесията
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                // Пренасочване към Dashboard
                header("Location: ../contacts/dashboard.php");
                exit;
            } else {
                // Теоретично не трябва да се стига до тук
                $error = "Регистрацията е успешна, но възникна грешка при автоматичния вход. Моля, влезте ръчно.";
            }
        } else {
            // Грешка (най-вероятно заето име)
            $error = "Грешка при регистрация. Потребителското име може да е заето.";
        }
    }
}
?>

<div class="auth-container">
    <h2 class="text-center">Регистрация</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <div class="form-group">
            <label>Потребителско име *</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Парола *</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Име *</label>
            <input type="text" name="first_name" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Фамилия *</label>
            <input type="text" name="last_name" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary btn-block">Регистрирай се</button>
    </form>
    <p style="text-align:center; margin-top:10px;">
        Имате акаунт? <a href="login.php" style="color:var(--primary-color)">Вход</a>
    </p>
</div>

<?php require_once '../layouts/footer.php'; ?>