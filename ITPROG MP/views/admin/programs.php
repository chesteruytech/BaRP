<?php
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../models/Benefit.php';
requireAdmin();

$db = portalDb();
$programs = Benefit::allWithRules($db);

$categories = ['Senior','Student','Indigent','Disaster'];
$sectors    = ['General','Senior Citizen','PWD','Student','Solo Parent','Indigent'];
$incomes    = ['Low','Middle','High'];

$pageTitle  = 'Programs';
$activePage = 'programs';
include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
    <div>
        <h2><i class="bi bi-gift"></i> Program Criteria Manager</h2>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#programModal"
            onclick="resetProgramForm()">
        <i class="bi bi-plus-lg"></i> New Program
    </button>
</div>


<div class="card">
    <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <span>All Programs</span>
        <input type="text" id="search" class="form-control form-control-sm" style="max-width:220px"
               placeholder="Search program..." onkeyup="searchTable()">
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0" id="dataTable">
            <thead>
                <tr>
                    <th>Program</th>
                    <th>Category</th>
                    <th>Eligibility Rule</th>
                    <th>Requirements</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($programs)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No programs configured yet.</td></tr>
                <?php else: foreach ($programs as $p): ?>
                    <?php
                        $ruleParts = [];
                        if ($p['minimum_age'] !== null) $ruleParts[] = 'Age &ge; ' . (int)$p['minimum_age'];
                        if ($p['maximum_age'] !== null) $ruleParts[] = 'Age &le; ' . (int)$p['maximum_age'];
                        if (!empty($p['required_sector'])) $ruleParts[] = 'Sector: ' . e($p['required_sector']);
                        if (!empty($p['required_income'])) $ruleParts[] = 'Income: ' . e($p['required_income']);
                        if (!empty($p['required_location'])) $ruleParts[] = 'Location: ' . e($p['required_location']);
                        if (!empty($p['household_limit'])) $ruleParts[] = 'One claim / household';
                        if (!empty($p['requires_disaster_affected'])) $ruleParts[] = 'Disaster-affected only';
                    ?>
                    <tr>
                        <td>
                            <strong><?= e($p['benefit_name']) ?></strong>
                            <div class="text-muted small"><?= e(mb_strimwidth($p['description'], 0, 90, '...')) ?></div>
                        </td>
                        <td><span class="badge text-bg-info"><?= e($p['category']) ?></span></td>
                        <td class="small"><?= $ruleParts ? implode('<br>', $ruleParts) : '<span class="text-muted">No rule set</span>' ?></td>
                        <td>
                            <span class="badge text-bg-secondary"><?= (int)$p['requirement_count'] ?> doc(s)</span>
                            <a href="requirements.php?benefit_id=<?= (int)$p['benefit_id'] ?>" class="btn btn-sm btn-outline-dark ms-1">
                                <i class="bi bi-list-check"></i> Manage
                            </a>
                        </td>
                        <td>
                            <?= $p['active']
                                ? '<span class="badge text-bg-success">Active</span>'
                                : '<span class="badge text-bg-secondary">Inactive</span>' ?>
                        </td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                    data-bs-toggle="modal" data-bs-target="#programModal"
                                    onclick='populateProgramForm(<?= json_encode([
                                        'id' => $p['benefit_id'],
                                        'name' => $p['benefit_name'],
                                        'description' => $p['description'],
                                        'category' => $p['category'],
                                        'active' => (bool)$p['active'],
                                        'minimum_age' => $p['minimum_age'],
                                        'maximum_age' => $p['maximum_age'],
                                        'required_sector' => $p['required_sector'],
                                        'required_income' => $p['required_income'],
                                        'required_location' => $p['required_location'],
                                        'household_limit' => (bool)$p['household_limit'],
                                        'requires_disaster_affected' => (bool)$p['requires_disaster_affected'],
                                    ]) ?>)'>
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <form action="../../controllers/BenefitCon.php" method="POST" class="d-inline"
                                  onsubmit="return confirmDelete()">
                                <input type="hidden" name="action" value="delete_program">
                                <input type="hidden" name="benefit_id" value="<?= (int)$p['benefit_id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add / Edit Program Modal -->
<div class="modal fade" id="programModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="../../controllers/BenefitCon.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="programModalTitle">New Program</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="save_program">
                    <input type="hidden" name="benefit_id" id="program_id" value="">

                    <div class="mb-3">
                        <label class="form-label">Program Name</label>
                        <input type="text" name="benefit_name" id="program_name" class="form-control" maxlength="150" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="program_description" class="form-control" rows="3" required></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category</label>
                            <select name="category" id="program_category" class="form-select">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= e($cat) ?>"><?= e($cat) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="active" id="program_active" checked>
                                <label class="form-check-label" for="program_active">Active (visible to residents)</label>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h6 class="text-muted">Eligibility Rule</h6>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Minimum Age</label>
                            <input type="number" min="0" name="minimum_age" id="program_min_age" class="form-control">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Maximum Age</label>
                            <input type="number" min="0" name="maximum_age" id="program_max_age" class="form-control">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Required Sector</label>
                            <select name="required_sector" id="program_sector" class="form-select">
                                <option value="">Any</option>
                                <?php foreach ($sectors as $s): ?>
                                    <option value="<?= e($s) ?>"><?= e($s) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Required Income</label>
                            <select name="required_income" id="program_income" class="form-select">
                                <option value="">Any</option>
                                <?php foreach ($incomes as $inc): ?>
                                    <option value="<?= e($inc) ?>"><?= e($inc) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Required Location</label>
                            <input type="text" name="required_location" id="program_location" class="form-control"
                                   placeholder="e.g. Purok 3 (matches part of resident address)">
                        </div>
                        <div class="col-md-3 mb-3 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="household_limit" id="program_household_limit">
                                <label class="form-check-label" for="program_household_limit">Limit to one claim per household</label>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="requires_disaster_affected" id="program_disaster">
                                <label class="form-check-label" for="program_disaster">Requires disaster-affected status</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-text">Leave a field blank / "Any" if it should not be checked for this program.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save Program</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function resetProgramForm() {
    document.getElementById('programModalTitle').textContent = 'New Program';
    document.getElementById('program_id').value = '';
    document.getElementById('program_name').value = '';
    document.getElementById('program_description').value = '';
    document.getElementById('program_category').value = 'Senior';
    document.getElementById('program_active').checked = true;
    document.getElementById('program_min_age').value = '';
    document.getElementById('program_max_age').value = '';
    document.getElementById('program_sector').value = '';
    document.getElementById('program_income').value = '';
    document.getElementById('program_location').value = '';
    document.getElementById('program_household_limit').checked = false;
    document.getElementById('program_disaster').checked = false;
}

function populateProgramForm(data) {
    document.getElementById('programModalTitle').textContent = 'Edit Program';
    document.getElementById('program_id').value = data.id;
    document.getElementById('program_name').value = data.name;
    document.getElementById('program_description').value = data.description;
    document.getElementById('program_category').value = data.category;
    document.getElementById('program_active').checked = !!data.active;
    document.getElementById('program_min_age').value = data.minimum_age ?? '';
    document.getElementById('program_max_age').value = data.maximum_age ?? '';
    document.getElementById('program_sector').value = data.required_sector ?? '';
    document.getElementById('program_income').value = data.required_income ?? '';
    document.getElementById('program_location').value = data.required_location ?? '';
    document.getElementById('program_household_limit').checked = !!data.household_limit;
    document.getElementById('program_disaster').checked = !!data.requires_disaster_affected;
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
