<?php
require_once __DIR__ . '/../../includes/helpers.php'; 
requireResident(); 
$db=portalDb(); 
$resident=currentResident($db); 

if(!$resident) die('Resident record not found.');

$types=$db->query('SELECT * 
                   FROM certificate_types 
                   ORDER BY certificate_name')->fetchAll();

$stmt=$db->prepare("SELECT cr.*,ct.certificate_name 
                    FROM certificate_requests cr 
                    JOIN certificate_types ct ON ct.certificate_type_id=cr.certificate_type_id 
                    WHERE cr.resident_id=? 
                    ORDER BY cr.request_date 
                    DESC"); 

$stmt->execute([$resident['resident_id']]); $requests=$stmt->fetchAll();

$pageTitle='Certificates & Clearances'; 
$activePage='certificates'; 

include __DIR__.'/../../includes/resident_header.php';
?>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">Request a Barangay Document</div>
            
            <div class="card-body">
                <form action="../../controllers/CertificateController.php" method="post"> 
                    <div class="mb-3">
                        <label class="form-label">Certificate / Clearance</label>
                        
                        <select name="certificate_type_id" class="form-select" required>
                            <option value="">Select document</option>
                            <?php foreach($types as $t): ?>
                            <option value="<?= (int)$t['certificate_type_id'] ?>"><?= e($t['certificate_name']) ?></option>
                            <?php endforeach ?>
                        </select>

                    </div>
                    <button class="btn btn-primary">Submit Request</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">My Requests</div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Request</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                    
                        <tbody>
                            <?php if(!$requests): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No requests yet.</td>
                            </tr>
                        
                            <?php else: foreach($requests as $r): ?>
                            <tr>
                                <td><?= e($r['certificate_name']) ?></td>
                                <td><?= e(date('M d, Y',strtotime($r['request_date']))) ?></td>
                                <td>
                                    <span class="badge text-bg-<?= $r['status']==='Approved'?'success':
                                        ($r['status']==='Rejected'?'danger':'warning') ?>"><?= e($r['status']) ?>
                                    </span>
                                </td>
                                <td><?= e($r['remarks']?:'—') ?></td>
                            </tr>
                            <?php endforeach; endif ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__.'/../../includes/resident_footer.php'; ?>
