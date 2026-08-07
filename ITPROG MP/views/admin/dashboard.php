<?php
require_once __DIR__ . '/../../includes/helpers.php';
requireAdmin();

$db = (new Database())->connect();

$totalResidents   = $db->query("SELECT COUNT(*) FROM residents")->fetchColumn();
$pendingApps      = $db->query("SELECT COUNT(*) FROM applications WHERE status='Pending'")->fetchColumn();
$pendingCerts     = $db->query("SELECT COUNT(*) FROM certificate_requests WHERE status='Pending'")->fetchColumn();
$activeAnnounce   = $db->query("SELECT COUNT(*) FROM announcements")->fetchColumn();

$recentApps = $db->query("
    SELECT a.application_id, CONCAT(r.first_name,' ',r.last_name) AS resident_name,
           b.benefit_name, a.status, a.application_date
    FROM applications a
    JOIN residents r ON r.resident_id = a.resident_id
    JOIN benefits b  ON b.benefit_id  = a.benefit_id
    ORDER BY a.application_date DESC
    LIMIT 5
")->fetchAll();

$pageTitle  = 'Dashboard';
$activePage = 'dashboard';
include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/sidebar.php';
?>

<h2><i class="bi bi-speedometer2"></i> Dashboard</h2>
<p class="text-muted">Overview of the barangay portal.</p>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card bg-blue">
            <i class="bi bi-people"></i>
            <h2><?= (int)$totalResidents ?></h2>
            <div>Total Residents</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card bg-yellow">
            <i class="bi bi-file-earmark-text"></i>
            <h2><?= (int)$pendingApps ?></h2>
            <div>Pending Applications</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card bg-red">
            <i class="bi bi-award"></i>
            <h2><?= (int)$pendingCerts ?></h2>
            <div>Pending Certificates</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card bg-green">
            <i class="bi bi-megaphone"></i>
            <h2><?= (int)$activeAnnounce ?></h2>
            <div>Announcements</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">Recent Applications</div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Resident</th>
                    <th>Program / Benefit</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentApps)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-3">No applications yet.</td></tr>
                <?php else: foreach ($recentApps as $app): ?>
                    <tr>
                        <td><?= htmlspecialchars($app['resident_name']) ?></td>
                        <td><?= htmlspecialchars($app['benefit_name']) ?></td>
                        <td><?= date('M d, Y', strtotime($app['application_date'])) ?></td>
                        <td>
                            <?php
                                $badge = $app['status'] === 'Approved' ? 'success'
                                       : ($app['status'] === 'Rejected' ? 'danger' : 'warning');
                            ?>
                            <span class="badge text-bg-<?= $badge ?>"><?= $app['status'] ?></span>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
