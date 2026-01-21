<?php
// shipments.php
require_once 'layouts/header.php';
require_once 'classes/Shipment.php';
require_once 'classes/Office.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'employee')) {
    header("Location: dashboard.php");
    exit;
}

$shipmentObj = new Shipment($pdo);
$officeObj = new Office($pdo);
$offices = $officeObj->getAll();
$message = '';

// Handle Registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $sender_id = 1; // Default to admin/current user if creating manual? Or select sender?
    // Requirement check: "Slujeitelite ... mogat da registrirat" 
    // Usually they select the sender from existing users or register a new one.
    // For simplicity, let's assume they enter Sender ID or Phone.
    // Let's use the current logged user as "Registrar" but we need the REAL sender. 
    // In this simplified version, let's pick a Sender ID from a list or input.
    $sender_id = $_POST['sender_id'];
    $receiver_phone = $_POST['receiver_phone'];
    $from_office_id = $_POST['from_office_id'];
    $weight = $_POST['weight'];

    $to_office_id = !empty($_POST['to_office_id']) ? $_POST['to_office_id'] : null;
    $to_address = !empty($_POST['to_address']) ? $_POST['to_address'] : null;

    if ($shipmentObj->register($sender_id, $receiver_phone, $from_office_id, $to_office_id, $to_address, $weight)) {
        $message = "Пратката е регистрирана успешно!";
    } else {
        $message = "Грешка при регистриране.";
    }
}

// Handle Status Update
if (isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $shipmentObj->updateStatus($_POST['shipment_id'], $_POST['status']);
    $message = "Статусът е обновен.";
}

$shipments = $shipmentObj->getAll();
?>

<h2>Управление на Пратки</h2>

<?php if ($message): ?>
    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="row">
    <div class="col" style="flex:1; margin-right:20px;">
        <div style="border: 1px solid #ddd; padding: 20px; border-radius: 8px;">
            <h3>Регистрирай пратка (Служебно)</h3>
            <form method="POST" action="shipments.php">
                <input type="hidden" name="action" value="register">

                <div class="form-group">
                    <label>ID на Подател (Клиент)</label>
                    <input type="number" name="sender_id" class="form-control" required placeholder="ID от списъка с клиенти">
                </div>

                <div class="form-group">
                    <label>Телефон на Получател</label>
                    <input type="text" name="receiver_phone" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>От Офис</label>
                    <select name="from_office_id" class="form-control" required>
                        <?php foreach ($offices as $o): ?>
                            <option value="<?= $o['id'] ?>"><?= htmlspecialchars($o['location_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="border-top:1px solid #eee; margin:10px 0; padding-top:10px;">
                    <strong>Дестинация (Избери едно)</strong>
                    <div class="form-group">
                        <label>До Офис</label>
                        <select name="to_office_id" class="form-control">
                            <option value="">-- Избери офис --</option>
                            <?php foreach ($offices as $o): ?>
                                <option value="<?= $o['id'] ?>"><?= htmlspecialchars($o['location_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="text-center">ИЛИ</div>
                    <div class="form-group">
                        <label>До Адрес</label>
                        <input type="text" name="to_address" class="form-control" placeholder="Ул., Номер, Град">
                    </div>
                </div>

                <div class="form-group">
                    <label>Тегло (кг)</label>
                    <input type="number" step="0.01" name="weight" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Регистрирай</button>
            </form>
        </div>
    </div>

    <div class="col" style="flex:2;">
        <h3>Всички пратки</h3>
        <table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse; font-size:0.9rem;">
            <thead>
                <tr style="background: #f0f0f0;">
                    <th>ID</th>
                    <th>Подател</th>
                    <th>От</th>
                    <th>До</th>
                    <th>Цена</th>
                    <th>Статус</th>
                    <th>Действие</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($shipments as $s): ?>
                    <tr>
                        <td><?= $s['id'] ?></td>
                        <td><?= htmlspecialchars($s['sender_name'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($s['from_office_name']) ?></td>
                        <td>
                            <?= $s['to_office_id'] ? 'Офис: ' . htmlspecialchars($s['to_office_name']) : 'Адрес: ' . htmlspecialchars($s['to_address']) ?>
                        </td>
                        <td><?= number_format($s['price'], 2) ?> лв.</td>
                        <td>
                            <span class="badge" style="background:#eee; padding:2px 5px; border-radius:3px;"><?= $s['status'] ?></span>
                        </td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="shipment_id" value="<?= $s['id'] ?>">
                                <select name="status" onchange="this.form.submit()" style="font-size:0.8rem;">
                                    <option value="registered" <?= $s['status'] == 'registered' ? 'selected' : '' ?>>Reg</option>
                                    <option value="in_transit" <?= $s['status'] == 'in_transit' ? 'selected' : '' ?>>Transit</option>
                                    <option value="delivered" <?= $s['status'] == 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                    <option value="canceled" <?= $s['status'] == 'canceled' ? 'selected' : '' ?>>Canceled</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'layouts/footer.php'; ?>