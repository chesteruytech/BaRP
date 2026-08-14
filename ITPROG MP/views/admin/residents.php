<?php
require_once __DIR__.'/../../includes/helpers.php'; 
requireAdmin(); 
$db=portalDb();

$rows=$db->query("SELECT r.*,h.household_number,h.address,h.income_level,u.email,u.status 
                  AS account_status 
                  FROM residents r 
                  JOIN households h ON h.household_id=r.household_id 
                  JOIN users u ON u.user_id=r.user_id 
                  ORDER BY r.last_name,r.first_name")->fetchAll();

$pageTitle='Residents'; 
$activePage='residents'; 

include __DIR__.'/../layouts/header.php'; 
include __DIR__.'/../layouts/sidebar.php';
?>

<h2><i class="bi bi-people"></i> Residents</h2>
<p class="text-muted">Verify resident accounts and maintain basic eligibility information.</p>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Resident</th>
                        <th>Household</th>
                        <th>Address</th>
                        <th>Eligibility Data</th>
                        <th>Verification</th>
                        <th>Update</th>
                    </tr>
                </thead>
                
                <tbody>
                    <?php foreach($rows as $r): ?>
                    <tr>
                        <td>
                            <strong><?= e($r['first_name'].' '.$r['last_name']) ?></strong>
                            <br><small><?= e($r['email']) ?></small>
                        </td>
                        <td><?= e($r['household_number']) ?></td>
                        <td><?= e($r['address']) ?></td>
                        <td>
                            <form class="row g-1" action="../../controllers/AdminCon.php" method="post">
                                <input type="hidden" name="action" value="update_resident">
                                <input type="hidden" name="resident_id" value="<?= (int)$r['resident_id'] ?>">
                                
                                <div class="col-12">
                                    <select class="form-select form-select-sm" name="sector">
                                        <?php foreach(['General','Senior Citizen','PWD','Student','Solo Parent','Indigent'] as $v): ?>
                                        <option <?= $r['sector']===$v?'selected':'' ?>><?= e($v) ?></option>
                                        <?php endforeach ?>
                                    </select>
                                </div>
                                
                                <div class="col-12">
                                    <select class="form-select form-select-sm" name="income_level">
                                        <?php foreach(['Low','Middle','High'] as $v): ?>
                                        <option <?= $r['income_level']===$v?'selected':'' ?>><?= e($v) ?></option>
                                        <?php endforeach ?>
                                    </select>
                                </div>
                        </td>
                        
                        <td>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="verified" id="v<?= (int)$r['resident_id'] ?>" 
                                    <?= $r['verified']?'checked':'' ?>>
                                <label class="form-check-label" for="v<?= (int)$r['resident_id'] ?>">Verified</label>
                            </div>
                            
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="disaster_affected" id="d<?= (int)$r['resident_id'] ?>" 
                                    <?= !empty($r['disaster_affected'])?'checked':'' ?>>
                                <label class="form-check-label" for="d<?= (int)$r['resident_id'] ?>">Disaster affected</label>
                            </div>
                        </td>
                        
                        <td>
                            <button class="btn btn-sm btn-primary">Save</button>
                            </form>
                        </td>
                    </tr>
                
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__.'/../layouts/footer.php'; ?>
