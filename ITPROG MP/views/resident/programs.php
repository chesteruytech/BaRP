<?php
require_once __DIR__ . "/../../includes/helpers.php";
require_once __DIR__ . "/../../models/Benefit.php";
require_once __DIR__ . "/../../models/Household.php";
require_once __DIR__ . "/../../models/Document.php";
requireResident();

$pageTitle = 'Programs';
$activePage = 'programs';
include __DIR__ . '/../../includes/resident_header.php';

$db = portalDb();
$resident = currentResident($db);

$filter = $_GET['category'] ?? 'All';
$categories = ['Senior', 'Student', 'Indigent', 'Disaster'];
$programs = Benefit::active($db, $filter !== 'All' ? $filter : null);

$members = [];
$documentsByResident = [];
if ($resident) {
    $members = Household::members($db, (int)$resident['household_id']);
    foreach ($members as $m) {
        $documentsByResident[(int)$m['resident_id']] = Document::forResident($db, (int)$m['resident_id']);
    }
}
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h2 class="mb-1">Barangay Programs & Assistance</h2>
        <p class="text-muted mb-0">Check your eligibility and apply for available benefits.</p>
    </div>

    <div class="btn-group flex-wrap">
        <a class="btn btn-sm <?= $filter === 'All' ? 'btn-primary' : 'btn-outline-primary' ?>" href="?category=All">All</a>
        <?php foreach ($categories as $c): ?>
            <a class="btn btn-sm <?= $filter === $c ? 'btn-primary' : 'btn-outline-primary' ?>" href="?category=<?= urlencode($c) ?>">
                <?= e($c) ?></a>
        <?php endforeach ?>
    </div>
</div>

<?php if (!$resident): ?>
    <div class="alert alert-warning">No resident record is linked to your account yet. Please contact the barangay office.</div>
<?php elseif (!$programs): ?>
    <div class="alert alert-light border">No programs available in this category right now.</div>
<?php endif; ?>

<div class="row g-3">
    <?php foreach ($programs as $p):
        $benefitId = (int)$p['benefit_id'];
        $rule = benefitRule($db, $benefitId);
        $requirements = benefitRequirements($db, $benefitId);
        $eligibility = $resident ? checkBenefitEligibility($db, $resident, $p) : ['eligible' => false, 'reasons' => ['No resident profile found.']];

        $ruleParts = [];
        if ($rule) {
            if ($rule['minimum_age'] !== null) $ruleParts[] = 'Age &ge; ' . (int)$rule['minimum_age'];
            if ($rule['maximum_age'] !== null) $ruleParts[] = 'Age &le; ' . (int)$rule['maximum_age'];
            if (!empty($rule['required_sector'])) $ruleParts[] = 'Sector: ' . e($rule['required_sector']);
            if (!empty($rule['required_income'])) $ruleParts[] = 'Household income: ' . e($rule['required_income']);
            if (!empty($rule['required_location'])) $ruleParts[] = 'Location: ' . e($rule['required_location']);
            if (!empty($rule['household_limit'])) $ruleParts[] = 'One claim per household';
            if (!empty($rule['requires_disaster_affected'])) $ruleParts[] = 'Disaster-affected residents only';
        }
    ?>
    <div class="col-md-6">
        <article class="card h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between gap-2">
                    <span class="badge text-bg-primary"><?= e($p['category']) ?></span>
                    <?php if ($eligibility['eligible']): ?>
                        <span class="badge text-bg-success"><i class="bi bi-check-circle"></i> You're eligible</span>
                    <?php else: ?>
                        <span class="badge text-bg-secondary">Check requirements</span>
                    <?php endif; ?>
                </div>

                <h4 class="mt-3"><?= e($p['benefit_name']) ?></h4>
                <p class="mb-2"><?= nl2br(e($p['description'])) ?></p>

                <?php if ($ruleParts): ?>
                    <div class="small text-muted mb-2"><?= implode(' &middot; ', $ruleParts) ?></div>
                <?php endif; ?>

                <?php if ($requirements): ?>
                    <div class="small mb-2">
                        <strong>Requirements:</strong>
                        <ul class="mb-0">
                            <?php foreach ($requirements as $r): ?>
                                <li><?= e($r['requirement_name']) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (!$eligibility['eligible'] && $resident): ?>
                    <div class="alert alert-warning small py-2 mb-2">
                        <?= implode('<br>', array_map('e', $eligibility['reasons'])) ?>
                    </div>
                <?php endif; ?>

                <div class="mt-auto pt-2">
                    <?php if ($resident): ?>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#applyModal<?= $benefitId ?>">
                            <i class="bi bi-send"></i> Apply
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </article>
    </div>

    <?php if ($resident): ?>
    <!-- Apply Modal for this program -->
    <div class="modal fade" id="applyModal<?= $benefitId ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="../../controllers/BenefitCon.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Apply: <?= e($p['benefit_name']) ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="apply">
                        <input type="hidden" name="benefit_id" value="<?= $benefitId ?>">

                        <?php if ($p['category'] === 'Student' && count($members) > 1): ?>
                            <div class="mb-3">
                                <label class="form-label">Applying for</label>
                                <select name="beneficiary_resident_id" class="form-select beneficiary-select" data-benefit="<?= $benefitId ?>">
                                    <?php foreach ($members as $m): ?>
                                        <option value="<?= (int)$m['resident_id'] ?>" <?= (int)$m['resident_id'] === (int)$resident['resident_id'] ? 'selected' : '' ?>>
                                            <?= e($m['first_name'] . ' ' . $m['last_name']) ?> &mdash; <?= e($m['sector']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Student Assistance may be claimed on behalf of a household member.</div>
                            </div>
                        <?php else: ?>
                            <input type="hidden" name="beneficiary_resident_id" value="<?= (int)$resident['resident_id'] ?>">
                        <?php endif; ?>

                        <?php if (!$requirements): ?>
                            <p class="text-muted small">No supporting documents required for this program.</p>
                        <?php else: ?>
                            <?php foreach ($requirements as $r): ?>
                                <div class="mb-3">
                                    <label class="form-label"><?= e($r['requirement_name']) ?></label>
                                    <select name="requirement_document[<?= (int)$r['requirement_id'] ?>]"
                                            class="form-select requirement-select" data-benefit="<?= $benefitId ?>" required>
                                        <option value="">-- Select an uploaded document --</option>
                                        <?php foreach (($documentsByResident[(int)$resident['resident_id']] ?? []) as $doc): ?>
                                            <option value="<?= (int)$doc['document_id'] ?>">
                                                <?= e($doc['document_name']) ?> (<?= e(date('M d, Y', strtotime($doc['upload_date']))) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endforeach; ?>
                            <div class="form-text mb-2">
                                Don't see the file you need? <a href="profile.php">Upload it in your Document Dashboard</a> first.
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Submit Application</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <?php endforeach ?>
</div>

<script>
// Documents available per household member, so the requirement dropdowns
// can switch when a different beneficiary (e.g. a student) is selected.
const householdDocs = <?= json_encode(array_map(function ($docs) {
    return array_map(function ($d) {
        return ['id' => (int)$d['document_id'], 'label' => $d['document_name'] . ' (' . date('M d, Y', strtotime($d['upload_date'])) . ')'];
    }, $docs);
}, $documentsByResident)) ?>;

document.querySelectorAll('.beneficiary-select').forEach(function (select) {
    select.addEventListener('change', function () {
        const benefitId = this.dataset.benefit;
        const residentId = this.value;
        const docs = householdDocs[residentId] || [];
        document.querySelectorAll('.requirement-select[data-benefit="' + benefitId + '"]').forEach(function (reqSelect) {
            reqSelect.innerHTML = '<option value="">-- Select an uploaded document --</option>';
            docs.forEach(function (doc) {
                const opt = document.createElement('option');
                opt.value = doc.id;
                opt.textContent = doc.label;
                reqSelect.appendChild(opt);
            });
            if (!docs.length) {
                reqSelect.innerHTML = '<option value="">No documents uploaded for this person</option>';
            }
        });
    });
});
</script>

<?php include __DIR__ . '/../../includes/resident_footer.php'; ?>
