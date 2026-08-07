<?php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../models/Document.php';
requireResident();
$db = portalDb();

$resident = currentResident($db);

if (!$resident) redirectWithMessage('../views/resident/documents.php','error','Resident record not found.');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
if ($action === 'upload' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['document_name'] ?? '');
    if ($name === '' || empty($_FILES['document']['name'])) 
        redirectWithMessage('../views/resident/documents.php','error','Document name and file are required.');

    $file = $_FILES['document'];
    if ($file['error'] !== UPLOAD_ERR_OK) 
        redirectWithMessage('../views/resident/documents.php','error','Upload failed.');

    if ((int)$file['size'] > 5 * 1024 * 1024) 
        redirectWithMessage('../views/resident/documents.php','error','File exceeds the 5 MB limit.');

    $ext = allowedUploadExtension($file['name']);
    if ($ext === '') 
        redirectWithMessage('../views/resident/documents.php','error','Only PDF, JPG, JPEG, and PNG files are allowed.');

    $safe = bin2hex(random_bytes(12)) . '.' . $ext;

    $dir = ensureUploadDirectory();
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $safe)) 
        redirectWithMessage('../views/resident/documents.php','error','Could not save uploaded file.');

    $relative = 'uploads/documents/' . $safe;
    $stmt = $db->prepare('INSERT INTO documents(resident_id,document_name,file_type,file_path) 
                          VALUES(?,?,?,?)');

    $stmt->execute([$resident['resident_id'],$name,$ext,$relative]);

    redirectWithMessage('../views/resident/documents.php','success','Document uploaded successfully.');
}

if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['document_id'] ?? 0);
    $stmt = $db->prepare('SELECT * FROM documents 
                          WHERE document_id=? AND resident_id=?');
    $stmt->execute([$id,$resident['resident_id']]);
    $doc = $stmt->fetch();

    if ($doc) {
        $db->prepare('DELETE FROM documents WHERE document_id=?')->execute([$id]);
        $path = __DIR__ . '/../' . $doc['file_path'];
        if (is_file($path)) @unlink($path);
    }
    
    redirectWithMessage('../views/resident/documents.php','success','Document removed.');
}

header('Location: ../views/resident/documents.php');
