<?php
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../models/Household.php';
requireResident();
$db=portalDb();
$resident=currentResident($db);

if(!$resident){ 
    die('Resident record not found.'); 
}

$members=Household::members($db,(int)$resident['household_id']);

$stmt=$db->prepare("SELECT a.*,b.benefit_name 
                    FROM applications a 
                    JOIN benefits b ON b.benefit_id=a.benefit_id 
                    WHERE a.resident_id=? 
                    ORDER BY a.application_date 
                    DESC LIMIT 10");
$stmt->execute([$resident['resident_id']]); 

$benefits=$stmt->fetchAll();
$stmt=$db->prepare("SELECT cr.*,ct.certificate_name 
                    FROM certificate_requests cr 
                    JOIN certificate_types ct ON ct.certificate_type_id=cr.certificate_type_id 
                    WHERE cr.resident_id=? 
                    ORDER BY cr.request_date 
                    DESC LIMIT 10");

$stmt->execute([$resident['resident_id']]); 
$certs=$stmt->fetchAll();

$pageTitle='My Profile'; 
$activePage='profile';
include __DIR__.'/../../includes/resident_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2 class="mb-1">My Profile</h2>
        <p class="text-muted mb-0">Personal, household, and eligibility information.</p>
    </div>
    
    <span class="badge text-bg-<?= $resident['verified']?'success':'warning' ?> fs-6">
        <?= $resident['verified']?'Verified Resident':'Pending Verification' ?>
    </span>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card">
            
            <div class="card-header">Resident Information</div>
            
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <strong>Full Name</strong>
                    <div>
                    <?= e(trim($resident['first_name'].' '.$resident['middle_name'].' '.$resident['last_name'])) ?> 
                </div>
            </div>

            <div class="col-md-3">
                <strong>Age</strong>
                <div><?= e(residentAge($resident['birthdate'])) ?></div>
            </div>

            <div class="col-md-3">
                <strong>Sector</strong>
                <div><?= e($resident['sector']) ?></div>
            </div>

            <div class="col-md-6">
                <strong>Email</strong>
                <div><?= e($resident['email']) ?></div>
            </div>
    
            <div class="col-md-6">
                <strong>Birthdate</strong>
                <div><?= e($resident['birthdate']) ?></div>
            </div>

            <div class="col-md-6">
                <strong>Gender</strong>
                <div><?= e($resident['gender']) ?></div>
            </div>

            <div class="col-md-6">
                <strong>Emergency/Disaster Affected</strong>
                <div><?= !empty($resident['disaster_affected'])?'Yes':'No' ?></div>
            </div>
        </div>
        
        <hr>
        
        <form method="post" action="../../controllers/ResidentCon.php" class="row g-3"> 
            <div class="col-md-6">
                <label class="form-label">Contact Number</label>
                <input class="form-control" name="contact_number" value="<?= e($resident['contact_number']) ?>">
            </div>

            <div class="col-md-6">
                <label class="form-label">Civil Status</label>
                <select class="form-select" name="civil_status"><?php foreach(['Single','Married','Widowed','Separated'] as $v): ?>
                    <option <?= $resident['civil_status']===$v?'selected':'' ?>><?= e($v) ?></option>
                    <?php endforeach ?>
                </select>
            </div>

            <div class="col-12">
                <button class="btn btn-primary">Update Contact Details</button>
            </div>
        </form>
    </div>
</div>
 
</div>
 
<div class="col-lg-5">
    <div class="card h-100">
        <div class="card-header">Household</div>
        
        <div class="card-body">
            <p><strong>Household No.:</strong> <?= e($resident['household_number']) ?></p>
            <p><strong>Address:</strong> <?= e($resident['address']) ?></p>
            <p><strong>Income Level:</strong> <?= e($resident['income_level']) ?></p>
            <hr><strong>Registered Household Members</strong>
            <ul class="mb-0 mt-2">
                <?php foreach($members as $m): ?>
                    <li><?= e($m['first_name'].' '.$m['last_name']) ?> 
                        <span class="text-muted">(<?= e($m['sector']) ?>)</span>
                    </li>
                <?php endforeach ?>
            </ul>
        </div>
    </div>
</div>

</div>

<div class="row g-4 mt-1">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">Benefit / Assistance History</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Program</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        
                        <tbody>
                            <?php if(!$benefits): ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">No benefit applications yet.</td>
                            </tr>
                            
                            <?php else: foreach($benefits as $a): ?>
                            <tr>
                                <td><?= e($a['benefit_name']) ?></td>
                                <td><?= e(date('M d, Y',strtotime($a['application_date']))) ?></td>
                                <td>
                                    <span class="badge text-bg-<?= $a['status']==='Approved'?'success':
                                    ($a['status']==='Rejected'?'danger':'warning') ?>"><?= e($a['status']) ?></span>
                                </td>
                            </tr>
                            
                            <?php endforeach; endif ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">Certificate Requests</div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Certificate</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        
                        <tbody>
                            <?php if(!$certs): ?>
                            <tr>
                                <td colspan="2" class="text-center text-muted py-3">No requests yet.</td>
                            </tr>
                            
                            <?php else: foreach($certs as $c): ?>   
                            <tr>
                                <td><?= e($c['certificate_name']) ?></td>
                                <td>
                                    <span class="badge text-bg-<?= $c['status']==='Approved'?'success':
                                    ($c['status']==='Rejected'?'danger':'warning') ?>"><?= e($c['status']) ?></span>
                                </td>
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
