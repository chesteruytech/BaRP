<?php
require_once __DIR__.'/../../includes/helpers.php'; 
requireAdmin(); 
$db=portalDb();

$rows=$db->query('SELECT * FROM resident_registry 
                  ORDER BY household_number,last_name,first_name')->fetchAll();

$pageTitle='Resident Registry'; 
$activePage='registry'; 

include __DIR__.'/../layouts/header.php'; 
include __DIR__.'/../layouts/sidebar.php';
?>

<h2><i class="bi bi-person-vcard"></i> Master Resident Registry</h2>
<p class="text-muted">Registration is automatically verified when name, birthdate, and address match a record here.</p>

<div class="card mb-4">
    <div class="card-header">Add Legitimate Barangay Resident</div>
        <div class="card-body">
            <form class="row g-3" action="../../controllers/AdminCon.php" method="post">
                <input type="hidden" name="action" value="add_registry">
                
                <div class="col-md-3">
                    <label class="form-label">Household No.</label>
                    <input class="form-control" name="household_number" required>
                </div>
                
                <div class="col-md-5">
                    <label class="form-label">Address</label>
                    <input class="form-control" name="address" required>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">First Name</label>
                    <input class="form-control" name="first_name" required>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Last Name</label>
                    <input class="form-control" name="last_name" required>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Birthdate</label>
                    <input type="date" class="form-control" name="birthdate" required>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Sector</label>
                    <select class="form-select" name="sector">
                        <?php foreach(['General','Senior Citizen','PWD','Student','Solo Parent','Indigent'] as $v): ?>
                        <option><?= e($v) ?></option>
                        <?php endforeach ?>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Income Level</label>
                    <select class="form-select" name="income_level">
                        <option>Low</option>
                        <option selected>Middle</option>
                        <option>High</option>
                    </select>
                </div>
                
                <div class="col-md-3 d-flex align-items-end">
                    <button class="btn btn-primary w-100">Add to Registry</button>
                </div>
            </form>
        </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Household</th>
                        <th>Resident</th>
                        <th>Birthdate</th>
                        <th>Address</th>
                        <th>Sector</th>
                        <th>Income</th>
                        <th></th>
                    </tr>
                </thead>
                
                <tbody>
                    <?php if(!$rows): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Registry is empty.</td>
                    </tr>
                    
                    <?php else: foreach($rows as $r): ?>
                    <tr>
                        <td><?= e($r['household_number']) ?></td>
                        <td><?= e($r['first_name'].' '.$r['last_name']) ?></td>
                        <td><?= e($r['birthdate']) ?></td>
                        <td><?= e($r['address']) ?></td>
                        <td><?= e($r['sector']) ?></td>
                        <td><?= e($r['income_level']) ?></td>
                        <td>
                            <form action="../../controllers/AdminCon.php" method="post" onsubmit="return confirm('Delete this registry entry?')">
                                <input type="hidden" name="action" value="delete_registry">
                                <input type="hidden" name="registry_id" value="<?= (int)$r['registry_id'] ?>">
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
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
