<?php
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../models/Application.php';
requireResident(); 
$db=portalDb(); 
$resident=currentResident($db); 

if(!$resident) die('Resident record not found.');

$applications=Application::forResident($db,(int)$resident['resident_id']);
$stmt=$db->prepare("SELECT cr.*,ct.certificate_name 
                    FROM certificate_requests cr 
                    JOIN certificate_types ct ON ct.certificate_type_id=cr.certificate_type_id 
                    WHERE cr.resident_id=? 
                    ORDER BY cr.request_date 
                    DESC"); 
$stmt->execute([$resident['resident_id']]); $certs=$stmt->fetchAll();
$pageTitle='Application Status'; 
$activePage='applications'; 

include __DIR__.'/../../includes/resident_header.php';
?>

<h2>Application Status Dashboard</h2>
<p class="text-muted">Track benefit applications and certificate requests in one place.</p>

<div class="card mb-4">
    <div class="card-header">Benefit Applications</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
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
                    <?php if(!$applications): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No applications submitted.</td>
                    </tr>
                    
                    <?php else: foreach($applications as $a): ?>
                    <tr>
                        <td>APP-<?= str_pad((string)$a['application_id'],6,'0',STR_PAD_LEFT) ?></td>
                        <td><?= e($a['benefit_name']) ?></td><td><?= e($a['beneficiary_name']) ?></td>
                        <td><?= e(date('M d, Y',strtotime($a['application_date']))) ?></td>
                        <td>
                            <span class="badge text-bg-<?= $a['status']==='Approved'?'success':
                                ($a['status']==='Rejected'?'danger':'warning') ?>"><?= e($a['status']) ?>
                            </span>
                        </td>
                        <td><?= e($a['remarks']?:'—') ?></td>
                    </tr>
                    
                    <?php endforeach; endif ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">Certificate / Clearance Requests</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Request #</th>
                        <th>Certificate</th>
                        <th>Date Submitted</th>
                        <th>Status</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                
                <tbody>
                    <?php if(!$certs): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No certificate requests submitted.</td>
                    </tr>
                    
                    <?php else: foreach($certs as $c): ?>
                    <tr>
                        <td>CERT-<?= str_pad((string)$c['request_id'],6,'0',STR_PAD_LEFT) ?></td>
                        <td><?= e($c['certificate_name']) ?></td>
                        <td><?= e(date('M d, Y',strtotime($c['request_date']))) ?></td>
                        <td>
                            <span class="badge text-bg-<?= $c['status']==='Approved'?'success':
                                ($c['status']==='Rejected'?'danger':'warning') ?>"><?= e($c['status']) ?>
                            </span>
                        </td>
                        <td><?= e($c['remarks']?:'—') ?></td>
                    </tr>
                    
                    <?php endforeach; endif ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__.'/../../includes/resident_footer.php'; ?>
