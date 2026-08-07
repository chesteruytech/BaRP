<?php
require_once __DIR__ . '/../includes/helpers.php';
requireResident();
$db = portalDb();

$resident = currentResident($db);

if (!$resident) 
    redirectWithMessage('../views/resident/certificates.php','error','Resident record not found.');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
    header('Location: ../views/resident/certificates.php'); 
    exit; 
}

$typeId = (int)($_POST['certificate_type_id'] ?? 0);
$stmt = $db->prepare('SELECT certificate_type_id 
                      FROM certificate_types 
                      WHERE certificate_type_id=?');
$stmt->execute([$typeId]);

if (!$stmt->fetch()) 
    redirectWithMessage('../views/resident/certificates.php','error','Invalid certificate type.');

$stmt = $db->prepare("SELECT COUNT(*) 
                      FROM certificate_requests 
                      WHERE resident_id=? 
                      AND certificate_type_id=? 
                      AND status='Pending'");
$stmt->execute([$resident['resident_id'],$typeId]);

if ((int)$stmt->fetchColumn() > 0) 
    redirectWithMessage('../views/resident/certificates.php','error','You already have a pending request for this certificate.');

$db->prepare('INSERT INTO certificate_requests(resident_id,certificate_type_id) 
              VALUES(?,?)')->execute([$resident['resident_id'],$typeId]);
              
redirectWithMessage('../views/resident/certificates.php','success','Certificate request submitted.');
