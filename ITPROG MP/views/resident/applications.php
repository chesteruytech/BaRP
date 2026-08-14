<?php
require_once __DIR__ . "/../../includes/helpers.php";
require_once __DIR__ . "/../../models/Application.php";
requireResident();

$pageTitle = 'Applications';
$activePage = 'applications';
include __DIR__ . '/../../includes/resident_header.php';

$db = portalDb();
$resident = currentResident($db);
$applications = $resident ? Application::forResident($db, (int)$resident['resident_id']) : [];
?>

<div class="mb-3">
    <h2 class="mb-1">My Applications</h2>
    <p class="text-muted mb-0">Track the status of every benefit you or your household have applied for.</p>
</div>

<?php if (!$applications): ?>
    <div class="alert alert-light border">
        No applications yet. Head to <a href="programs.php">Programs</a> to apply for a benefit.
    </div>
<?php else: ?>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Application #</th>
                    <th>Program</th>
                    <th>Beneficiary</th>
                    <th>Date Submitted</th>
                    <th>Status</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applications as $a):
                    $badge = $a['status'] === 'Approved' ? 'success' : ($a['status'] === 'Rejected' ? 'danger' : 'warning');
                ?>
                <tr>
                    <td>#<?= (int)$a['application_id'] ?></td>
                    <td>
                        <?= e($a['benefit_name']) ?>
                        <div class="text-muted small"><?= e($a['category']) ?></div>
                    </td>
                    <td><?= e($a['beneficiary_name']) ?></td>
                    <td><?= e(date('M d, Y', strtotime($a['application_date']))) ?></td>
                    <td><span class="badge text-bg-<?= $badge ?>"><?= e($a['status']) ?></span></td>
                    <td class="small text-muted"><?= $a['remarks'] ? e($a['remarks']) : '&mdash;' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/resident_footer.php'; ?>
