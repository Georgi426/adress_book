<?php
require_once 'layouts/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$message = '';

// Обработка на действията: Създаване или Изтриване на поле
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Изтриване
    if (isset($_POST['delete_id'])) {
        // Изтриваме дефиницията на полето (свързаните стойности също ще трябва да се изчистят или се разчита на ON DELETE CASCADE в базата)
        $stmt = $pdo->prepare("DELETE FROM custom_field_definitions WHERE id = :id AND user_id = :user_id");
        if ($stmt->execute([':id' => $_POST['delete_id'], ':user_id' => $_SESSION['user_id']])) {
            $message = "Полето е изтрито.";
        }
    } else {
        // Създаване на ново поле
        $field_name = trim($_POST['field_name']);
        $field_type = $_POST['field_type'];

        if (!empty($field_name)) {
            $stmt = $pdo->prepare("INSERT INTO custom_field_definitions (user_id, field_name, field_type) VALUES (:user_id, :field_name, :field_type)");
            if ($stmt->execute([':user_id' => $_SESSION['user_id'], ':field_name' => $field_name, ':field_type' => $field_type])) {
                $message = "Полето е добавено.";
            }
        }
    }
}

// Извличане на съществуващите полета за показване в списъка
$stmt = $pdo->prepare("SELECT * FROM custom_field_definitions WHERE user_id = :user_id");
$stmt->execute([':user_id' => $_SESSION['user_id']]);
$fields = $stmt->fetchAll();
?>

<h2>Персонализирани Полета</h2>
<p>Тук можете да дефинирате допълнителни полета, които искате да имате за вашите контакти (напр. Рожден ден, LinkedIn профил и др.).</p>

<?php if ($message): ?>
    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-4">
        <div class="card" style="padding: 15px; border: 1px solid #ddd;">
            <h3>Ново поле</h3>
            <form method="POST" action="custom_fields.php">
                <div class="form-group">
                    <label>Име на полето</label>
                    <input type="text" name="field_name" class="form-control" placeholder="Напр. Рожден ден" required>
                </div>
                <div class="form-group">
                    <label>Тип данни</label>
                    <select name="field_type" class="form-control">
                        <option value="text">Текст</option>
                        <option value="date">Дата</option>
                        <option value="number">Число</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Добави</button>
            </form>
        </div>
    </div>

    <div class="col-8">
        <h3>Вашите полета</h3>
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f0f0f0;">
                        <th style="padding: 10px; border: 1px solid #ddd;">Име на поле</th>
                        <th style="padding: 10px; border: 1px solid #ddd;">Тип</th>
                        <th style="padding: 10px; border: 1px solid #ddd;">Действие</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($fields) > 0): ?>
                        <?php foreach ($fields as $field): ?>
                            <tr>
                                <td style="padding: 10px; border: 1px solid #ddd;"><?= htmlspecialchars($field['field_name']) ?></td>
                                <td style="padding: 10px; border: 1px solid #ddd;"><?= $field['field_type'] ?></td>
                                <td style="padding: 10px; border: 1px solid #ddd;">
                                    <form method="POST" action="custom_fields.php" onsubmit="return confirm('ВНИМАНИЕ: Изтриването на това поле ще изтрие и всички въведени стойности за него във всички контакти! Сигурни ли сте?')">
                                        <input type="hidden" name="delete_id" value="<?= $field['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Изтрий</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" style="padding: 10px;">Няма добавени допълнителни полета.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'layouts/footer.php'; ?>