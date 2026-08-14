<?php
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../models/Document.php';
requireResident();
$db=portalDb(); $resident=currentResident($db); if(!$resident) die('Resident record not found.');
$documents=Document::forResident($db,(int)$resident['resident_id']);
$pageTitle='Document Dashboard'; $activePage='documents'; include __DIR__.'/../../includes/resident_header.php';
?>
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">Upload Residency / Requirement Document</div>
            
            <div class="card-body">
                <p class="text-muted">Store clear scans or photos for future program applications. PDF/JPG/PNG, maximum 5 MB.</p>
                <form action="../../controllers/DocumentController.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="upload">
                    
                    <div class="mb-3">
                        <label class="form-label">Document Name</label>
                        <input name="document_name" class="form-control" placeholder="e.g., Barangay ID" required>
                    </div>
                        
                    <div class="mb-3">
                        <label class="form-label">File</label>
                        <input type="file" name="document" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                    </div>
                            
                    <button class="btn btn-primary"><i class="bi bi-upload"></i> Upload Document</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">My Stored Documents</div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Uploaded</th>
                                <th>File</th>
                                <th></th>
                            </tr>
                        </thead>
                        
                        <tbody>
                            <?php if(!$documents): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No documents uploaded yet.</td>
                            </tr>
                            
                            <?php else: foreach($documents as $d): ?>
                            <tr>
                                <td><?= e($d['document_name']) ?></td>
                                <td><?= e(date('M d, Y',strtotime($d['upload_date']))) ?></td>
                                <td><a class="btn btn-sm btn-outline-primary" target="_blank" href="../../<?= e($d['file_path']) ?>">View</a></td>
                                <td>
                                    <form action="../../controllers/DocumentController.php" method="post" onsubmit="return confirm('Remove this document?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="document_id" value="<?= (int)$d['document_id'] ?>">
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
    </div>
</div>

<?php include __DIR__.'/../../includes/resident_footer.php'; ?>
