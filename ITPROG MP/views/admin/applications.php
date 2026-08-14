<?php
require_once __DIR__.'/../../includes/helpers.php'; 
requireAdmin(); 
$db=portalDb(); 

$rows=$db->query("SELECT a.*,b.benefit_name,
                  CONCAT(r.first_name,' ',r.last_name) resident_name,
                  CONCAT(s.first_name,' ',s.last_name) submitter_name 
                  FROM applications a 
                  JOIN benefits b ON b.benefit_id=a.benefit_id 
                  JOIN residents r ON r.resident_id=a.resident_id 
                  LEFT JOIN residents s ON s.resident_id=a.submitted_by_resident_id 
                  ORDER BY a.application_date DESC")->fetchAll();

$pageTitle='Applications'; 
$activePage='applications'; 

include __DIR__.'/../layouts/header.php'; 
include __DIR__.'/../layouts/sidebar.php';
?>

<h2><i class="bi bi-file-earmark-text"></i> Benefit Applications</h2>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Application</th>
                        <th>Resident / Program</th>
                        <th>Date</th>
                        <th>Attached Documents</th>
                        <th>Decision & Remarks</th>
                    </tr>
                </thead>
                
                <tbody>
                    <?php if(!$rows): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No applications.</td>
                    </tr>
                    
                    <?php else: foreach($rows as $a): ?>
                    <tr>
                        <td>APP-<?= str_pad((string)$a['application_id'],6,'0',STR_PAD_LEFT) ?>
                        <br>
                            <span class="badge text-bg-<?= $a['status']==='Approved'?'success':($a['status']==='Rejected'?'danger':'warning') ?>">
                            <?= e($a['status']) ?></span>
                        </td>
                        
                        <td>
                            <strong><?= e($a['resident_name']) ?></strong>
                            <br>
                            <?= e($a['benefit_name']) ?>
                            <?php if($a['submitter_name'] && $a['submitter_name']!==$a['resident_name']): ?>
                                
                            <br>
                            <small>Submitted by <?= e($a['submitter_name']) ?></small>
                            <?php endif ?></td><td><?= e(date('M d, Y',strtotime($a['application_date']))) ?>
                        </td>
                        
                        <td>
                            <?php $ds=$db->prepare("SELECT ad.file_path,br.requirement_name 
                                                    FROM application_documents ad 
                                                    JOIN benefit_requirements br ON br.requirement_id=ad.requirement_id 
                                                    WHERE ad.application_id=?"); 
                                $ds->execute([$a['application_id']]); 
                                $attached=$ds->fetchAll(); 
                            
                            if(!$attached): ?><span class="text-muted">None</span>
                                <?php else: foreach($attached as $d): ?>
                            
                            <a class="d-block small" target="_blank" href="../../<?= e($d['file_path']) ?>">
                                <?= e($d['requirement_name']) ?>
                            </a>
                            
                            <?php endforeach; endif ?>
                        </td>
                        
                        <td>
                            <form action="../../controllers/AdminCon.php" method="post" class="row g-2">
                                <input type="hidden" name="action" value="application_status">
                                <input type="hidden" name="application_id" value="<?= (int)$a['application_id'] ?>">
                                
                                <div class="col-md-4"><select class="form-select form-select-sm" name="status">
                                    <?php foreach(['Pending','Approved','Rejected'] as $v): ?>
                                    <option <?= $a['status']===$v?'selected':'' ?>><?= e($v) ?></option>
                                    <?php endforeach ?></select></div><div class="col-md-6">
                                    <input class="form-control form-control-sm" name="remarks" value="<?= e($a['remarks']) ?>" placeholder="Remarks / missing requirement">
                                </div>
                                
                                <div class="col-md-2">
                                    <button class="btn btn-sm btn-primary w-100">Save</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                    
                    <?php endforeach; endif ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__.'/../layouts/footer.php'; ?>
