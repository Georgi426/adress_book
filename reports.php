<?php
// reports.php
require_once 'layouts/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Helper queries
// 1. All Employees
$stmt = $pdo->query("SELECT * FROM users WHERE role = 'employee'");
$allEmployees = $stmt->fetchAll();

// 2. All Clients
$stmt = $pdo->query("SELECT * FROM users WHERE role = 'client'");
$allClients = $stmt->fetchAll();

// 3. All Shipments (filtered by date typically, but here all)
$stmt = $pdo->query("SELECT count(*) as total, sum(price) as revenue FROM shipments");
$stats = $stmt->fetch();

// 4. Revenue for period
$stmtRev = $pdo->prepare("SELECT sum(price) as revenue FROM shipments WHERE date_created BETWEEN ? AND ?");
$stmtRev->execute([$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
$periodRevenue = $stmtRev->fetch()['revenue'] ?? 0;

// 5. Sent but not received (In Transit or Registered)
$stmtPending = $pdo->query("SELECT * FROM shipments WHERE status IN ('registered', 'in_transit')");
$pendingShipments = $stmtPending->fetchAll();

?>

<h2>Справки</h2>

<div class="row">
    <div class="col-6">
        <div class="card" style="padding:15px; border:1px solid #ddd; margin-bottom:20px;">
            <h3>Финансов отчет</h3>
            <form method="GET" action="reports.php" style="display:flex; gap:10px; align-items:center;">
                <input type="date" name="start_date" value="<?= $start_date ?>" class="form-control">
                <span>до</span>
                <input type="date" name="end_date" value="<?= $end_date ?>" class="form-control">
                <button type="submit" class="btn btn-primary">Покажи</button>
            </form>
            <p style="font-size:1.2rem; margin-top:10px;">
                Приходи за периода: <strong><?= number_format($periodRevenue, 2) ?> лв.</strong>
            </p>
            <p>Общо приходи (от началото): <?= number_format($stats['revenue'], 2) ?> лв.</p>
        </div>
    </div>

    <div class="col-6">
        <div class="card" style="padding:15px; border:1px solid #ddd; margin-bottom:20px;">
            <h3>Обща статистика</h3>
            <ul>
                <li>Служители: <?= count($allEmployees) ?></li>
                <li>Клиенти: <?= count($allClients) ?></li>
                <li>Общо пратки: <?= $stats['total'] ?></li>
            </ul>
        </div>
    </div>
</div>

<h3>Пратки на път (Неполучени)</h3>
<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse; font-size:0.9rem;">
    <thead>
        <tr style="background: #f0f0f0;">
            <th>ID</th>
            <th>Дата</th>
            <th>Статус</th>
            <th>Цена</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($pendingShipments as $s): ?>
            <tr>
                <td><?= $s['id'] ?></td>
                <td><?= $s['date_created'] ?></td>
                <td><?= $s['status'] ?></td>
                <td><?= $s['price'] ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Additional reports can be tabs or separate sections -->

<?php require_once 'layouts/footer.php'; ?>