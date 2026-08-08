<?php
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../models/Benefit.php';
requireAdmin();

$db = portalDb();

$benefitId = (int)($_GET['benefit_id'] ?? 0);
$benefit   = $benefitId ? Benefit::find($db, $benefitId) : null;
$allPrograms = $db->query("SELECT benefit_id, benefit_name, category FROM benefits ORDER BY category, benefit_name")->fetchAll();

$requirements = $benefit ? benefitRequirements($db, $benefitId) : [];

$pageTitle  = 'Requirements Builder';
$activePage = 'requirements';
include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/sidebar.php';
?>

<h2><i class="bi bi-list-check"></i> Requirements Checklist Builder</h2>

<div class="card mb-4">
    <div class="card-body">
        <label class="form-label">Select a Program</label>
        <div class="d-flex gap-2 flex-wrap">
            <?php foreach ($allPrograms as $p): ?>
                <a href="requirements.php?benefit_id=<?= (int)$p['benefit_id'] ?>"
                   class="btn btn-sm <?= $benefitId === (int)$p['benefit_id'] ? 'btn-dark' : 'btn-outline-dark' ?>">
                    <?= e($p['benefit_name']) ?> <span class="badge text-bg-light text-dark"><?= e($p['category']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>



<?php if (!$benefit): ?>
    <div class="alert alert-info">Select a program above to manage its required documents.</div>
<?php else: ?>

    <div class="card">
        <div class="card-header">
            Required Documents for <strong><?= e($benefit['benefit_name']) ?></strong>
        </div>
        <div class="card-body">
            <form action="../../controllers/BenefitCon.php" method="POST" class="row g-2 mb-4">
                <input type="hidden" name="action" value="add_requirement">
                <input type="hidden" name="benefit_id" value="<?= (int)$benefit['benefit_id'] ?>">
                <div class="col-md-9">
                    <input type="text" name="requirement_name" class="form-control"
                           placeholder="e.g. Barangay Clearance, Valid ID, Senior Citizen ID" maxlength="100" required>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-plus-lg"></i> Add Requirement
                    </button>
                </div>
            </form>

            
            <ul class="list-group">
                <?php if (empty($requirements)): ?>
                    <li class="list-group-item text-muted text-center">No document requirements added yet.</li>
                <?php else: foreach ($requirements as $req): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-file-earmark-text me-2"></i><?= e($req['requirement_name']) ?></span>
                        <form action="../../controllers/BenefitCon.php" method="POST"
                              onsubmit="return confirmDelete()">
                            <input type="hidden" name="action" value="delete_requirement">
                            <input type="hidden" name="requirement_id" value="<?= (int)$req['requirement_id'] ?>">
                            <input type="hidden" name="benefit_id" value="<?= (int)$benefit['benefit_id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </li>
                <?php endforeach; endif; ?>
            </ul>
        </div>
    </div>

<?php endif; ?>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
