<?php
require_once __DIR__ . "/../../includes/helpers.php";
requireResident();

$pageTitle = 'Certificate Requests';
$activePage = 'certificates';

$db = portalDb();
$resident = currentResident($db);

$types = $db->query("SELECT certificate_type_id, certificate_name FROM certificate_types ORDER BY certificate_name")->fetchAll();

$stmt = $db->prepare("SELECT cr.request_id, ct.certificate_name, cr.status, cr.request_date
                            FROM certificate_requests cr
                            JOIN certificate_types ct ON ct.certificate_type_id = cr.certificate_type_id
                            WHERE cr.resident_id = ?
                            ORDER BY cr.request_date DESC");
$stmt->execute([$resident['resident_id']]);
$requests = $stmt->fetchAll();

include __DIR__ . '/../../includes/resident_header.php';
?>

    <div class="mb-3">
        <h2 class="mb-1">Certificate Requests</h2>
        <p class="text-muted mb-0">Request barangay documents for your needs.</p>
    </div>

    <div class="mb-3">
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#requestModal">
            Request a Barangay Document
        </button>
    </div>

    <div class="modal fade" id="requestModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="../../controllers/CertificateController.php" method="post">
                    <div class="modal-header">
                        <h5 class="modal-title">Request a Barangay Document</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label for="certificate_type_id" class="form-label">Document Type</label>
                        <select name="certificate_type_id" id="certificate_type_id" class="form-select" required>
                            <option value="" disabled selected>Select a document...</option>
                            <?php foreach ($types as $type): ?>
                                <option value="<?= e($type['certificate_type_id']) ?>">
                                    <?= e($type['certificate_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <table class="table table-bordered">
        <thead>
        <tr><th>Document</th><th>Status</th><th>Requested On</th></tr>
        </thead>
        <tbody>
        <?php if (!$requests): ?>
            <tr><td colspan="3" class="text-muted">No requests yet.</td></tr>
        <?php else: foreach ($requests as $r): ?>
            <tr>
                <td><?= e($r['certificate_name']) ?></td>
                <td><?= e($r['status']) ?></td>
                <td><?= e($r['request_date']) ?></td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>

<?php include __DIR__ . '/../../includes/resident_footer.php'; ?>