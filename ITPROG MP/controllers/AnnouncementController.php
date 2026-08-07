<?php
require_once __DIR__ . '/../includes/helpers.php';
requireAdmin();
$db=portalDb();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
    header('Location: ../views/admin/announcements.php'); 
    exit; 
}

$action=$_POST['action']??'';

if ($action==='save') {
    $id=(int)($_POST['announcement_id']??0); 
    $title=trim($_POST['title']??''); 
    $description=trim($_POST['description']??'');

    $category=$_POST['category']??'General'; 
    $eventDate=trim($_POST['event_date']??'')?:null;

    if ($title===''||$description==='') 
        redirectWithMessage('../views/admin/announcements.php','error','Title and description are required.');

    if ($id) $db->prepare('UPDATE announcements 
                           SET title=?,description=?,category=?,event_date=? 
                           WHERE announcement_id=?')->execute([$title,$description,$category,$eventDate,$id]);

    else $db->prepare('INSERT INTO announcements(title,description,category,event_date,admin_id) 
                       VALUES(?,?,?,?,?)')->execute([$title,$description,$category,$eventDate,$_SESSION['user_id']]);

    redirectWithMessage('../views/admin/announcements.php','success','Announcement saved.');
}

if ($action==='delete') {
    $db->prepare('DELETE FROM announcements 
                  WHERE announcement_id=?')->execute([(int)($_POST['announcement_id']??0)]);

    redirectWithMessage('../views/admin/announcements.php','success','Announcement deleted.');
}

header('Location: ../views/admin/announcements.php');
