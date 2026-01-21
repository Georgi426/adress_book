<?php
// my_shipments.php
require_once 'layouts/header.php';
require_once 'classes/Shipment.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: dashboard.php");
    exit;
}

$shipmentObj = new Shipment($pdo);
$myShipments = $shipmentObj->getBySenderId($_SESSION['user_id']);

?>

<h2>Моите Пратки</h2>

<table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr style="background: #f0f0f0;">
            <th>ID</th>
            <th>Дата</th>
            <th>От</th>
            <th>До</th>
            <th>Тегло</th>
            <th>Цена</th>
            <th>Статус</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($myShipments) > 0): ?>
            <?php foreach ($myShipments as $s): ?>
                <tr>
                    <td><?= $s['id'] ?></td>
                    <td><?= date('d.m.Y H:i', strtotime($s['date_created'])) ?></td>
                    <td><?= htmlspecialchars($s['from_office_name']) ?></td>
                    <td>
                        <?= $s['to_office_id'] ? 'Офис: ' . htmlspecialchars($s['to_office_name']) : 'Адрес: ' . htmlspecialchars($s['to_address']) ?>
                    </td>
                    <td><?= $s['weight'] ?> кг</td>
                    <td><?= number_format($s['price'], 2) ?> лв.</td>
                    <td>
                        <span class="badge"><?= $s['status'] ?></span>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="text-center">Нямате регистрирани пратки.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php require_once 'layouts/footer.php'; ?>