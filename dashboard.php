<?php
// dashboard.php
require_once 'layouts/header.php';
require_once 'classes/User.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$role = $_SESSION['role'];
$username = $_SESSION['username'];
?>

<div class="row">
    <div class="col-12">
        <h2>Здравей, <?= htmlspecialchars($username) ?> (<?= htmlspecialchars($role) ?>)</h2>
    </div>
</div>

<div class="dashboard-menu" style="margin-top: 20px; display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">

    <!-- Admin & Employee Menu -->
    <?php if ($role === 'admin' || $role === 'employee'): ?>
        <div class="card" style="padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
            <h3><i class="fas fa-building"></i> Офиси</h3>
            <p>Управление на офиси на компанията.</p>
            <a href="offices.php" class="btn btn-primary">Към Офиси</a>
        </div>

        <div class="card" style="padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
            <h3><i class="fas fa-box-open"></i> Пратки</h3>
            <p>Регистрирай и проследи пратки.</p>
            <a href="shipments.php" class="btn btn-primary">Към Пратки</a>
        </div>

        <div class="card" style="padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
            <h3><i class="fas fa-users"></i> Клиенти</h3>
            <p>Списък с клиенти и справки.</p>
            <a href="clients.php" class="btn btn-primary">Към Клиенти</a>
        </div>
    <?php endif; ?>

    <!-- Admin Only -->
    <?php if ($role === 'admin'): ?>
        <div class="card" style="padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
            <h3><i class="fas fa-user-tie"></i> Служители</h3>
            <p>Управление на служителите.</p>
            <a href="employees.php" class="btn btn-primary">Към Служители</a>
        </div>
        <div class="card" style="padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
            <h3><i class="fas fa-chart-line"></i> Справки</h3>
            <p>Финансови и други отчети.</p>
            <a href="reports.php" class="btn btn-primary">Към Справки</a>
        </div>
    <?php endif; ?>

    <!-- Client Menu -->
    <?php if ($role === 'client'): ?>
        <div class="card" style="padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
            <h3><i class="fas fa-box"></i> Моите пратки</h3>
            <p>Преглед на изпратени и получени пратки.</p>
            <a href="my_shipments.php" class="btn btn-primary">Виж пратките</a>
        </div>
    <?php endif; ?>

</div>

<?php require_once 'layouts/footer.php'; ?>