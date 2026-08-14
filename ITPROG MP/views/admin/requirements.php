<?php
require_once __DIR__.'/../../includes/helpers.php'; 
requireAdmin(); 
$db=portalDb(); 

$benefits=$db->query('SELECT benefit_id,benefit_name 
                      FROM benefits 
                      ORDER BY benefit_name')->fetchAll(); 
                      
$selected=(int)($_GET['benefit_id']??($benefits[0]['benefit_id']??0)); 
$requirements=$selected?benefitRequirements($db,$selected):[];

$pageTitle='Requirements Builder'; 
$activePage='requirements'; 

include __DIR__.'/../layouts/header.php'; 
include __DIR__.'/../layouts/sidebar.php';
?>

<h2><i class="bi bi-list-check"></i> Requirements Checklist Builder</h2>
<p class="text-muted">Requirements are text-based, matching the project specification.</p>

<form class="mb-3" method="get">
    <label class="form-label">Program</label>
    <select class="form-select" name="benefit_id" onchange="this.form.submit()">
        <?php foreach($benefits as $b): ?>
        <option value="<?= (int)$b['benefit_id'] ?>" <?= $selected==(int)$b['benefit_id']?'selected':'' ?>>
            <?= e($b['benefit_name']) ?></option>
        <?php endforeach ?>
    </select>
</form>

<?php if($selected): ?>
    
<div class="card mb-3">
    <div class="card-body">
        <form class="d-flex gap-2" action="../../controllers/BenefitCon.php" method="post">
            <input type="hidden" name="action" value="add_requirement">
            <input type="hidden" name="benefit_id" value="<?= $selected ?>">
            <input class="form-control" name="requirement_name" placeholder="Required physical document" required>
            <button class="btn btn-primary">Add Requirement</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Requirement</th>
                    <th></th>
                </tr>
            </thead>
            
            <tbody>
                <?php if(!$requirements): ?>
                <tr>
                    <td colspan="2" class="text-center text-muted py-4">No requirements configured.</td>
                </tr>
                
                <?php else: foreach($requirements as $r): ?>
                <tr>
                    <td><?= e($r['requirement_name']) ?></td>
                    <td class="text-end">
                        <form action="../../controllers/BenefitCon.php" method="post">
                            <input type="hidden" name="action" value="delete_requirement">
                            <input type="hidden" name="benefit_id" value="<?= $selected ?>">
                            <input type="hidden" name="requirement_id" value="<?= (int)$r['requirement_id'] ?>">
                            <button class="btn btn-sm btn-outline-danger">Remove</button>
                        </form>
                    </td>
                </tr>
                
                <?php endforeach; endif ?>
            </tbody>
        </table>
    </div>
</div>

<?php endif ?>
<?php include __DIR__.'/../layouts/footer.php'; ?>
