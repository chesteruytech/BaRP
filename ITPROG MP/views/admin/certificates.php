<?php
require_once __DIR__.'/../../includes/helpers.php'; 
requireAdmin(); 
$db=portalDb(); 

$rows=$db->query("SELECT cr.*,ct.certificate_name,
                  CONCAT(r.first_name,' ',r.last_name) resident_name 
                  FROM certificate_requests cr 
                  JOIN certificate_types ct ON ct.certificate_type_id=cr.certificate_type_id 
                  JOIN residents r ON r.resident_id=cr.resident_id 
                  ORDER BY cr.request_date 
                  DESC")->fetchAll();

$pageTitle='Certificate Requests'; 
$activePage='certificates'; 

include __DIR__.'/../layouts/header.php'; 
include __DIR__.'/../layouts/sidebar.php';
?>

<h2><i class="bi bi-award"></i> Certificate & Clearance Requests</h2>

<div class="card">

    <div class="card-body p-0">
        
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Request</th>
                        <th>Resident</th>
                        <th>Document</th>
                        <th>Date</th>
                        <th>Decision</th>
                    </tr>
                </thead>
                
                <tbody>
                    <?php if(!$rows): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No requests.</td>
                        </tr>
                    <?php else: foreach($rows as $r): ?>
                        <tr>
                            <td>CERT-<?= str_pad((string)$r['request_id'],6,'0',STR_PAD_LEFT) ?></td>
                            <td><?= e($r['resident_name']) ?></td><td><?= e($r['certificate_name']) ?></td>
                            <td><?= e(date('M d, Y',strtotime($r['request_date']))) ?></td>

                            <td>
                                <form action="../../controllers/AdminCon.php" method="post" class="row g-1">
                                    <input type="hidden" name="action" value="certificate_status">
                                    <input type="hidden" name="request_id" value="<?= (int)$r['request_id'] ?>">
                                    
                                    <div class="col-4">
                                        <select class="form-select form-select-sm" name="status">
                                            <?php foreach(['Pending','Approved','Rejected'] as $v): ?>
                                            <option <?= $r['status']===$v?'selected':'' ?>><?= e($v) ?></option>
                                            <?php endforeach ?>
                                        </select>
                                    </div>
                                    
                                    <div class="col-6">
                                        <input class="form-control form-control-sm" name="remarks" value="<?= e($r['remarks']) ?>" placeholder="Remarks">
                                    </div>
                                    
                                    <div class="col-2">
                                        <button class="btn btn-sm btn-primary">Save</button>
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
