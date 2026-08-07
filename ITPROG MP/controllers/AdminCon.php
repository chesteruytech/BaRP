<?php
require_once __DIR__ . '/../includes/helpers.php';
requireAdmin();
$db=portalDb();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
    header('Location: ../views/admin/dashboard.php'); 
    exit; 
}

$action=$_POST['action']??'';

if ($action==='update_resident') {
    $residentId=(int)($_POST['resident_id']??0); 
    $verified=isset($_POST['verified'])?1:0;
    $sector=$_POST['sector']??'General'; 
    $income=$_POST['income_level']??'Middle'; 
    $affected=isset($_POST['disaster_affected'])?1:0;

    $stmt=$db->prepare('SELECT household_id FROM residents WHERE resident_id=?'); 
    $stmt->execute([$residentId]); 
    $householdId=$stmt->fetchColumn();

    if ($householdId) {
        $db->prepare('UPDATE residents 
                      SET verified=?,sector=?,disaster_affected=? 
                      WHERE resident_id=?')->execute([$verified,$sector,$affected,$residentId]);

        $db->prepare('UPDATE households 
                      SET income_level=? 
                      WHERE household_id=?')->execute([$income,$householdId]);
    }
    redirectWithMessage('../views/admin/residents.php','success','Resident record updated.');
}

if ($action==='application_status') {
    $id=(int)($_POST['application_id']??0); 
    $status=$_POST['status']??'Pending'; 
    $remarks=trim($_POST['remarks']??'')?:null;

    if (!in_array($status,['Pending','Approved','Rejected'],true)) $status='Pending';

    $db->prepare('UPDATE applications 
                  SET status=?,remarks=?,updated_at=NOW() 
                  WHERE application_id=?')->execute([$status,$remarks,$id]);

    redirectWithMessage('../views/admin/applications.php','success','Application status updated.');
}

if ($action==='certificate_status') {
    $id=(int)($_POST['request_id']??0); 
    $status=$_POST['status']??'Pending'; 
    $remarks=trim($_POST['remarks']??'')?:null;

    if (!in_array($status,['Pending','Approved','Rejected'],true)) $status='Pending';

    $db->prepare('UPDATE certificate_requests 
                  SET status=?,remarks=?,updated_at=NOW() 
                  WHERE request_id=?')->execute([$status,$remarks,$id]);

    redirectWithMessage('../views/admin/certificates.php','success','Certificate request updated.');
}

if ($action==='add_registry') {
    $fields=['household_number','address','first_name','last_name','birthdate','sector','income_level'];

    foreach($fields as $f) 
    if(trim((string)($_POST[$f]??''))==='') redirectWithMessage('../views/admin/registry.php','error','Complete all registry fields.');

    try {
        $db->prepare('INSERT INTO resident_registry(household_number,address,first_name,last_name,birthdate,sector,income_level) 
                      VALUES(?,?,?,?,?,?,?)')

           ->execute([trim($_POST['household_number']),
                      trim($_POST['address']),trim($_POST['first_name']),
                      trim($_POST['last_name']),$_POST['birthdate'],$_POST['sector'],$_POST['income_level']]);

        redirectWithMessage('../views/admin/registry.php','success','Master resident record added.');
    } catch(Throwable $e){ 
        redirectWithMessage('../views/admin/registry.php','error','That master resident record already exists or is invalid.'); 
    }
}

if ($action==='delete_registry') {
    try { 
        $db->prepare('DELETE FROM resident_registry 
                      WHERE registry_id=?')->execute([(int)($_POST['registry_id']??0)]); 

        redirectWithMessage('../views/admin/registry.php','success','Registry record removed.'); 
    }
    catch(Throwable $e){ 
        redirectWithMessage('../views/admin/registry.php','error','Registry record is linked to a registered resident and cannot be removed.'); 
    }
}

header('Location: ../views/admin/dashboard.php');
