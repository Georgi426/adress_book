<?php
// contact_edit.php
require_once 'layouts/header.php';
require_once 'classes/Contact.php';
require_once 'classes/Tag.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$contactObj = new Contact($pdo);
$tagObj = new Tag($pdo);

$id = $_GET['id'] ?? null;
$contact = null;
$contactTags = [];
$customFieldValues = [];

// Load existing data if editing
if ($id) {
    $contact = $contactObj->getById($id, $_SESSION['user_id']);
    if (!$contact) {
        echo "Контактът не е намерен.";
        require_once 'layouts/footer.php';
        exit;
    }
    $tagsRaw = $contactObj->getTags($id);
    $contactTags = array_column($tagsRaw, 'id');
}

$allTags = $tagObj->getAll($_SESSION['user_id']);

// Get Custom Fields Definitions
$stmt = $pdo->prepare("SELECT * FROM custom_field_definitions WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$customFieldDefs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle Form Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'first_name' => $_POST['first_name'],
        'last_name' => $_POST['last_name'],
        'company_name' => $_POST['company_name'],
        'address' => $_POST['address'],
        'phone_landline' => $_POST['phone_landline'],
        'phone_mobile' => $_POST['phone_mobile'],
        'email' => $_POST['email'],
        'fax' => $_POST['fax'],
        'note' => $_POST['note']
    ];

    if ($id) {
        $contactObj->update($id, $_SESSION['user_id'], $data);
        $contact_id = $id;
    } else {
        $contact_id = $contactObj->create($_SESSION['user_id'], $data);
    }

    if ($contact_id) {
        // Handle Tags
        if ($id) {
            $currentTags = $contactObj->getTags($contact_id);
            foreach ($currentTags as $ct) {
                $contactObj->detachTag($contact_id, $ct['id']);
            }
        }

        if (isset($_POST['tags'])) {
            foreach ($_POST['tags'] as $tag_id) {
                $contactObj->attachTag($contact_id, $tag_id);
            }
        }

        // Handle Custom Fields
        foreach ($customFieldDefs as $def) {
            if (isset($_POST['custom_' . $def['id']])) {
                $contactObj->saveCustomField($contact_id, $def['id'], $_POST['custom_' . $def['id']]);
            }
        }

        header("Location: dashboard.php");
        exit;
    }
}

// Prepare custom values for display
$currentCustomValues = [];
if ($id) {
    $cFields = $contactObj->getCustomFields($id);
    foreach ($cFields as $cf) {
        $currentCustomValues[$cf['def_id']] = $cf['value'];
    }
}

?>

<h2><?= $id ? 'Редактиране на контакт' : 'Нов контакт' ?></h2>

<form method="POST" action="contact_edit.php<?= $id ? '?id=' . $id : '' ?>">
    <div class="row">
        <div class="col-6">
            <div class="form-group">
                <label>Име *</label>
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
                <label>Мобилен телефон</label>
                <input type="text" name="phone_mobile" class="form-control" value="<?= htmlspecialchars($contact['phone_mobile'] ?? '') ?>">
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                <label>Стационарен телефон</label>
                <input type="text" name="phone_landline" class="form-control" value="<?= htmlspecialchars($contact['phone_landline'] ?? '') ?>">
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

    <!-- Custom Fields -->
    <?php if (!empty($customFieldDefs)): ?>
        <hr>
        <h4>Допълнителни полета</h4>
        <?php foreach ($customFieldDefs as $def): ?>
            <div class="form-group">
                <label><?= htmlspecialchars($def['field_name']) ?></label>
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

    <!-- Tags -->
    <hr>
    <h4>Етикети</h4>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <?php foreach ($allTags as $tag): ?>
            <label style="padding: 5px 10px; border: 1px solid #ddd; border-radius: 4px; background: whitesmoke; display: flex; align-items: center; gap: 5px; cursor: pointer;">
                <input type="checkbox" name="tags[]" value="<?= $tag['id'] ?>" <?= in_array($tag['id'], $contactTags) ? 'checked' : '' ?>>
                <span class="color-dot" style="width: 10px; height: 10px; background-color: <?= $tag['color'] ?>; border-radius: 50%; display: inline-block;"></span>
                <?= htmlspecialchars($tag['name']) ?>
            </label>
        <?php endforeach; ?>
    </div>
    <div style="margin-top: 10px;">
        <a href="tags.php" target="_blank" style="font-size: 0.9rem;"><i class="fas fa-cog"></i> Управление на етикети</a>
    </div>

    <!-- Note (Plain Text) -->
    <hr>
    <div class="form-group">
        <label>Коментар / Бележки</label>
        <textarea name="note" id="note" class="form-control" rows="5"><?= htmlspecialchars($contact['note'] ?? '') ?></textarea>
    </div>

    <button type="submit" class="btn btn-primary btn-block" style="margin-top: 20px;">Запази Контакта</button>
</form>

<?php require_once 'layouts/footer.php'; ?>