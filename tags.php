<?php

require_once 'layouts/header.php';
require_once 'classes/Tag.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$tagObj = new Tag($pdo);
$message = '';

// 2. ОБРАБОТКА НА ЗАЯВКИ (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // СЛУЧАЙ А: ИЗТРИВАНЕ НА ЕТИКЕТ
    // Ако във формата има поле 'delete_id', значи потребителят е натиснал бутона "Изтрий" (X)
    if (isset($_POST['delete_id'])) {
        if ($tagObj->delete($_POST['delete_id'], $_SESSION['user_id'])) {
            $message = "Етикетът е изтрит успешно.";
        }
    }
    // СЛУЧАЙ Б: СЪЗДАВАНЕ ИЛИ РЕДАКЦИЯ
    else {
        $name = trim($_POST['name']); // Изчистваме празните места
        $color = $_POST['color'];     // Цвета идва от <input type="color">

        if (!empty($name)) {
            // Проверяваме дали имаме ID. Ако има -> РЕДАКЦИЯ на съществуващ запис.
            // Това ID се попълва от JavaScript функцията editTag() при клик на бутон "Редакция".
            if (isset($_POST['id']) && !empty($_POST['id'])) {
                if ($tagObj->update($_POST['id'], $_SESSION['user_id'], $name, $color)) {
                    $message = "Етикетът е обновен.";
                }
            }
            // Ако няма ID --> СЪЗДАВАНЕ на нов запис.
            else {
                if ($tagObj->create($_SESSION['user_id'], $name, $color)) {
                    $message = "Етикетът е добавен.";
                }
            }
        }
    }
}

// 3. ЗАРЕЖДАНЕ НА ДАННИ
// Извличаме всички етикети на текущия потребител, за да ги покажем в таблицата долу.
$tags = $tagObj->getAll($_SESSION['user_id']);
?>

<h2>Управление на Етикети</h2>

<?php if ($message): ?>
    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="row">
    <!-- ЛЯВА КОЛОНА: ФОРМА ЗА ДОБАВЯНЕ/РЕДАКЦИЯ -->
    <div class="col-6">
        <div class="card" style="padding: 15px; border: 1px solid #ddd;">
            <h3>Добави / Редактирай Етикет</h3>


            <form method="POST" action="tags.php">
                <input type="hidden" name="id" id="tag_id">

                <div class="form-group">
                    <label>Име на етикет</label>
                    <input type="text" name="name" id="tag_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Цвят</label>
                    <input type="color" name="color" id="tag_color" class="form-control" style="height: 40px;" value="#3498db">
                </div>

                <button type="submit" class="btn btn-primary" id="save_btn">Запази</button>
                <button type="button" class="btn btn-secondary" onclick="resetForm()" style="display:none;" id="cancel_btn">Отказ</button>
            </form>
        </div>
    </div>

    <div class="col-6">
        <h3>Съществуващи Етикети</h3>
        <div class="table-responsive">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid #ddd;">
                        <th style="text-align: left; padding: 5px;">Цвят</th>
                        <th style="text-align: left; padding: 5px;">Име</th>
                        <th style="text-align: right; padding: 5px;">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tags as $tag): ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 10px;">
                                <span style="display: inline-block; width: 20px; height: 20px; background-color: <?= $tag['color'] ?>; border-radius: 50%;"></span>
                            </td>
                            <td style="padding: 10px; font-weight: bold;"><?= htmlspecialchars($tag['name']) ?></td>
                            <td style="padding: 10px; text-align: right;">
                                <button class="btn btn-sm btn-info" onclick="editTag(<?= $tag['id'] ?>, '<?= htmlspecialchars($tag['name']) ?>', '<?= $tag['color'] ?>')">Редакция</button>

                                <!-- Форма за изтриване с потвърждение -->
                                <form method="POST" action="tags.php" style="display:inline;" onsubmit="return confirm('Сигурни ли сте?')">
                                    <input type="hidden" name="delete_id" value="<?= $tag['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">X</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- JAVASCRIPT ЛОГИКА ЗА UI -->
<script>
    // Функция: Подготвя формата за РЕДАКЦИЯ
    // Взима данните от реда в таблицата и ги слага в полетата на формата.
    function editTag(id, name, color) {
        document.getElementById('tag_id').value = id; // Задаваме ID, за да знае PHP кой запис да промени
        document.getElementById('tag_name').value = name;
        document.getElementById('tag_color').value = color;

        // Променяме текста на бутона и показваме бутона "Отказ"
        document.getElementById('save_btn').innerText = 'Обнови';
        document.getElementById('cancel_btn').style.display = 'inline-block';
    }
    // Функция: Изчиства формата (връща я в режим СЪЗДАВАНЕ)
    function resetForm() {
        document.getElementById('tag_id').value = '';
        document.getElementById('tag_name').value = '';
        document.getElementById('save_btn').innerText = 'Запази';
        document.getElementById('cancel_btn').style.display = 'none';
    }
</script>

<?php require_once 'layouts/footer.php'; ?>