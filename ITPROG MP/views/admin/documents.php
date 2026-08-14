<?php
require_once __DIR__.'/../../includes/helpers.php'; 
requireAdmin(); 
$db=portalDb(); 

$rows=$db->query("SELECT d.*,
                  CONCAT(r.first_name,' ',r.last_name) resident_name 
                  FROM documents d 
                  JOIN residents r ON r.resident_id=d.resident_id 
                  ORDER BY d.upload_date 
                  DESC")->fetchAll();

$pageTitle='Resident Documents'; 
$activePage='documents'; 

include __DIR__.'/../layouts/header.php'; 
include __DIR__.'/../layouts/sidebar.php';
?>

<h2><i class="bi bi-folder2-open"></i> Resident Documents</h2>

<div class="card">
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Resident</th>
                    <th>Document</th>
                    <th>Type</th>
                    <th>Uploaded</th>
                    <th>File</th>
                </tr>
            </thead>
            
            <tbody>
                <?php if(!$rows): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No uploaded documents.</td>
                    </tr>
                    
                    <?php else: foreach($rows as $d): ?>
                    <tr>
                        <td><?= e($d['resident_name']) ?></td>
                        <td><?= e($d['document_name']) ?></td>
                        <td><?= e(strtoupper($d['file_type'])) ?></td>
                        <td><?= e(date('M d, Y',strtotime($d['upload_date']))) ?></td>
                        <td><a target="_blank" class="btn btn-sm btn-outline-primary" href="../../<?= e($d['file_path']) ?>">View</a></td>
                    </tr>
                    <?php endforeach; endif ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__.'/../layouts/footer.php'; ?>
