<?php
require_once __DIR__ . '/../includes/helpers.php';
requireResident();
$db=portalDb();

$resident=currentResident($db);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$resident) { 
    header('Location: ../views/resident/profile.php'); 
    exit; 
}

$contact=trim($_POST['contact_number']??''); 
$civil=$_POST['civil_status']??$resident['civil_status'];

$db->prepare('UPDATE residents 
              SET contact_number=?,civil_status=? 
              WHERE resident_id=?')->execute([$contact,$civil,$resident['resident_id']]);
              
redirectWithMessage('../views/resident/profile.php','success','Profile details updated.');
