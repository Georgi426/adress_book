<?php
// dashboard.php
require_once 'layouts/header.php';
require_once 'classes/Contact.php';
require_once 'classes/Tag.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$contactObj = new Contact($pdo);
$tagObj = new Tag($pdo);

$search = $_GET['search'] ?? null;
$contacts = $contactObj->getAll($_SESSION['user_id'], $search);
?>

<div class="row" style="justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div class="col-6">
        <h2>Моите Контакти</h2>
    </div>
    <div class="col-6" style="text-align: right;">
        <a href="contact_edit.php" class="btn btn-primary"><i class="fas fa-plus"></i> Нов Контакт</a>
    </div>
</div>

<form method="GET" action="dashboard.php" style="margin-bottom: 20px; display: flex; gap: 10px;">
    <input type="text" name="search" class="form-control" placeholder="Търсене по име, фирма, email..." value="<?= htmlspecialchars($search ?? '') ?>" style="flex:1;">
    <button type="submit" class="btn btn-secondary">Търси</button>
    <?php if ($search): ?>
        <a href="dashboard.php" class="btn btn-danger">Изчисти</a>
    <?php endif; ?>
</form>

<?php if (empty($contacts)): ?>
    <p>Няма намерени контакти. Добавете първия си контакт!</p>
<?php else: ?>
    <div class="row">
        <?php foreach ($contacts as $contact): ?>
            <?php
            $tags = $contactObj->getTags($contact['id']);
            ?>
            <div class="col-4" style="margin-bottom: 20px;">
                <div class="card" style="padding: 15px; border: 1px solid #ddd; height: 100%;">
                    <h3 style="margin-top: 0;">
                        <?= htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']) ?>
                    </h3>
                    <?php if ($contact['company_name']): ?>
                        <p style="color: #666; font-style: italic;"><?= htmlspecialchars($contact['company_name']) ?></p>
                    <?php endif; ?>

                    <div style="margin: 10px 0;">
                        <?php foreach ($tags as $tag): ?>
                            <span style="background-color: <?= $tag['color'] ?>; color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.8rem;">
                                <?= htmlspecialchars($tag['name']) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>

                    <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($contact['email'] ?? 'N/A') ?></p>
                    <p><i class="fas fa-phone"></i> <?= htmlspecialchars($contact['phone_mobile'] ?? 'N/A') ?></p>

                    <div style="margin-top: 15px; border-top: 1px solid #eee; padding-top: 10px; text-align: right;">
                        <a href="contact_edit.php?id=<?= $contact['id'] ?>" class="btn btn-sm btn-info">Редакция</a>
                        <a href="contact_delete.php?id=<?= $contact['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Сигурни ли сте?')">Изтрий</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once 'layouts/footer.php'; ?>