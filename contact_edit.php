<?php


require_once 'layouts/header.php';
require_once 'classes/Contact.php';
require_once 'classes/Tag.php';

// Проверка за автентикация: Ако потребителят не е влязъл в системата (няма сесия), го пренасочваме към страницата за вход (login.php).
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
// Инициализиране на обектите за връзка с базата данни
$contactObj = new Contact($pdo);
$tagObj = new Tag($pdo);

// Проверка дали сме в режим "Редакция" или "Нов контакт".
// Ако има параметър 'id' в URL адреса (напр. contact_edit.php?id=5), значи редактираме.
$id = $_GET['id'] ?? null;

// Инициализиране на променливи за данните
$contact = null;
$contactTags = []; // Тук ще пазим ID-тата на избраните етикети
$customFieldValues = []; // Тук ще пазим стойностите на допълнителните полета
$error = ''; // Променлива за съхранение на грешки при валидация


// ЗАРЕЖДАНЕ НА ДАННИ ПРИ РЕДАКЦИЯ
if ($id) {
    // Опитваме се да намерим контакта в базата данни по неговото ID и ID на текущия потребител.
    // за да не може да се редактира чужд контакт.
    $contact = $contactObj->getById($id, $_SESSION['user_id']);

    // Ако контактът не е намерен (или не принадлежи на потребителя)
    if (!$contact) {
        echo "Контактът не е намерен.";
        require_once 'layouts/footer.php';
        exit;
    }
    // Ако контактът е намерен, зареждаме и неговите етикети (Tags)
    $tagsRaw = $contactObj->getTags($id);
    // Преобразуваме масива, за да вземем само ID-тата на етикетите (напр. [1, 5, 8])
    $contactTags = array_column($tagsRaw, 'id');
}

// Зареждане на всички възможни етикети, създадени от потребителя.
// Те ще се използват за показване на checkbox списъка във формата.
$allTags = $tagObj->getAll($_SESSION['user_id']);

// Зареждане на дефинициите за допълнителни полета (Custom Fields), ако има такива.
$stmt = $pdo->prepare("SELECT * FROM custom_field_definitions WHERE user_id = :user_id");
$stmt->execute([':user_id' => $_SESSION['user_id']]);
$customFieldDefs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ОБРАБОТКА НА ФОРМАТА ПРИ ИЗПРАЩАНЕ (POST заявка)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Вземане и изчистване на данните
    $phone_mobile = trim($_POST['phone_mobile'] ?? '');
    $phone_landline = trim($_POST['phone_landline'] ?? '');

    // Валидация на Мобилен телефон
    if (!empty($phone_mobile) && !preg_match('/^[0-9]{10}$/', $phone_mobile)) {
        $error = "Мобилният телефон трябва да съдържа точно 10 цифри.";
    }

    // Валидация на Стационарен телефон
    if (!empty($phone_landline) && !preg_match('/^[0-9]{3,15}$/', $phone_landline)) {
        $error = "Стационарният телефон трябва да съдържа само цифри (мин. 3).";
    }

    if (empty($error)) {
        // Събиране на данните от полетата на формата в асоциативен масив
        $data = [
            'first_name' => $_POST['first_name'],
            'last_name' => $_POST['last_name'],
            'company_name' => $_POST['company_name'],
            'address' => $_POST['address'],
            'phone_landline' => $phone_landline,
            'phone_mobile' => $phone_mobile,
            'email' => $_POST['email'],
            'fax' => $_POST['fax'],
            'note' => $_POST['note']
        ];

        if ($id) {
            // СЛУЧАЙ РЕДАКЦИЯ: Обновяваме съществуващия запис
            $contactObj->update($id, $_SESSION['user_id'], $data);
            $contact_id = $id; // Запазваме ID-то за по-нататъшна употреба
        } else {
            // СЛУЧАЙ НОВ КОНТАКТ: Създаваме нов запис в базата
            $contact_id = $contactObj->create($_SESSION['user_id'], $data);
        }

        // Ако записът е успешен (имаме валидно ID на контакт)
        if ($contact_id) {

            //  Обработка на ЕТИКЕТИ 
            if ($id) {
                // При редакция, първо "откачаме" всички стари етикети, за да запишем актуалното състояние.
                // Това е по-лесно отколкото да проверяваме кои са добавени и кои премахнати.
                $currentTags = $contactObj->getTags($contact_id);
                foreach ($currentTags as $ct) {
                    $contactObj->detachTag($contact_id, $ct['id']);
                }
            }

            // Ако във формата са избрани етикети, ги записваме (свързваме) с контакта
            if (isset($_POST['tags'])) {
                foreach ($_POST['tags'] as $tag_id) {
                    $contactObj->attachTag($contact_id, $tag_id);
                }
            }

            //  Обработка на ДОПЪЛНИТЕЛНИ ПОЛЕТА 
            foreach ($customFieldDefs as $def) {
                // Проверяваме дали във формата има стойност за съответното поле (custom_ID)
                if (isset($_POST['custom_' . $def['id']])) {
                    $contactObj->saveCustomField($contact_id, $def['id'], $_POST['custom_' . $def['id']]);
                }
            }

            // Успешен край -> Пренасочване към Таблото 
            header("Location: dashboard.php");
            exit;
        }
    } else {
        // Ако има грешка, попълваме данните обратно в $contact, за да не се загубят
        $contact = [
            'first_name' => $_POST['first_name'] ?? '',
            'last_name' => $_POST['last_name'] ?? '',
            'company_name' => $_POST['company_name'] ?? '',
            'address' => $_POST['address'] ?? '',
            'phone_landline' => $phone_landline,
            'phone_mobile' => $phone_mobile,
            'email' => $_POST['email'] ?? '',
            'fax' => $_POST['fax'] ?? '',
            'note' => $_POST['note'] ?? ''
        ];
    }
}

// Подготовка на текущите стойности на допълнителните полета за визуализация във формата (при редакция)
$currentCustomValues = [];
if ($id && empty($error)) { // Зареждаме ги само ако е редакция и няма грешка от POST
    $cFields = $contactObj->getCustomFields($id);
    foreach ($cFields as $cf) {
        // Мапваме ID на дефиницията към нейната стойност
        $currentCustomValues[$cf['def_id']] = $cf['value'];
    }
}

?>

<!-- Заглавие на страницата: Различно според режима (Нов vs Редакция) -->
<h2><?= $id ? 'Редактиране на контакт' : 'Нов контакт' ?></h2>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<!-- Форма за въвеждане на данни -->
<form method="POST" action="contact_edit.php<?= $id ? '?id=' . $id : '' ?>">
    <div class="row">
        <div class="col-6">
            <div class="form-group">
                <label>Име *</label>
                <!-- required атрибутът задължава попълването на полето -->
                <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($contact['first_name'] ?? '') ?>" required>
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                <label>Фамилия *</label>
                <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($contact['last_name'] ?? '') ?>" required>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label>Фирма</label>
        <input type="text" name="company_name" class="form-control" value="<?= htmlspecialchars($contact['company_name'] ?? '') ?>">
    </div>

    <div class="form-group">
        <label>Адрес</label>
        <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($contact['address'] ?? '') ?></textarea>
    </div>

    <div class="row">
        <div class="col-6">
            <div class="form-group">
                <label>Мобилен телефон (10 цифри)</label>
                <!-- pattern="[0-9]{10}" задължава точно 10 цифри за HTML5 валидация -->
                <input type="text" name="phone_mobile" class="form-control" value="<?= htmlspecialchars($contact['phone_mobile'] ?? '') ?>" pattern="[0-9]{10}" title="Моля въведете точно 10 цифри (напр. 0888123456)">
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                <label>Стационарен телефон</label>
                <input type="text" name="phone_landline" class="form-control" value="<?= htmlspecialchars($contact['phone_landline'] ?? '') ?>" pattern="[0-9]{3,15}" title="Въведете само цифри">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-6">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($contact['email'] ?? '') ?>">
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                <label>Факс</label>
                <input type="text" name="fax" class="form-control" value="<?= htmlspecialchars($contact['fax'] ?? '') ?>">
            </div>
        </div>
    </div>

    <!-- Секция за Допълнителни полета (Показва се само ако има дефинирани такива) -->
    <?php if (!empty($customFieldDefs)): ?>
        <hr>
        <h4>Допълнителни полета</h4>
        <?php foreach ($customFieldDefs as $def): ?>
            <div class="form-group">
                <label><?= htmlspecialchars($def['field_name']) ?></label>
                <!-- Проверка на типа на полето за правилна визуализация (date/number/text) -->
                <?php if ($def['field_type'] === 'date'): ?>
                    <input type="date" name="custom_<?= $def['id'] ?>" class="form-control" value="<?= htmlspecialchars($currentCustomValues[$def['id']] ?? '') ?>">
                <?php elseif ($def['field_type'] === 'number'): ?>
                    <input type="number" name="custom_<?= $def['id'] ?>" class="form-control" value="<?= htmlspecialchars($currentCustomValues[$def['id']] ?? '') ?>">
                <?php else: ?>
                    <input type="text" name="custom_<?= $def['id'] ?>" class="form-control" value="<?= htmlspecialchars($currentCustomValues[$def['id']] ?? '') ?>">
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Секция за избор на Етикети -->
    <hr>
    <h4>Етикети</h4>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <?php foreach ($allTags as $tag): ?>
            <label style="padding: 5px 10px; border: 1px solid #ddd; border-radius: 4px; background: whitesmoke; display: flex; align-items: center; gap: 5px; cursor: pointer;">
                <!-- Checkbox за избор. in_array проверява дали този етикет вече е прикачен към контакта -->
                <input type="checkbox" name="tags[]" value="<?= $tag['id'] ?>" <?= in_array($tag['id'], $contactTags) ? 'checked' : '' ?>>

                <!-- Цветна точка за визуално ориентиране -->
                <span class="color-dot" style="width: 10px; height: 10px; background-color: <?= $tag['color'] ?>; border-radius: 50%; display: inline-block;"></span>
                <?= htmlspecialchars($tag['name']) ?>
            </label>
        <?php endforeach; ?>
    </div>
    <div style="margin-top: 10px;">
        <a href="tags.php" target="_blank" style="font-size: 0.9rem;"><i class="fas fa-cog"></i> Управление на етикети</a>
    </div>

    <!-- Секция за бележки -->
    <hr>
    <div class="form-group">
        <label>Коментар / Бележки</label>
        <!-- Текстова област, която ще бъде превърната в Rich Text Editor -->
        <textarea name="note" id="note" class="form-control" rows="5"><?= htmlspecialchars($contact['note'] ?? '') ?></textarea>
    </div>

    <button type="submit" class="btn btn-primary btn-block" style="margin-top: 20px;">Запази Контакта</button>
</form>

<?php require_once 'layouts/footer.php'; ?>