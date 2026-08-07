<?php
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../models/Announcement.php';
requireAdmin();

$db = portalDb();

$filter = $_GET['category'] ?? 'All';
$announcements = Announcement::all($db, $filter !== 'All' ? $filter : null);

$categories = ['General','Senior','Student','PWD','Solo Parent','Indigent'];

$pageTitle  = 'Announcements';
$activePage = 'announcements';
include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
    <div>
        <h2><i class="bi bi-megaphone"></i> Announcement Management</h2>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#announcementModal"
            onclick="resetAnnouncementForm()">
        <i class="bi bi-plus-lg"></i> New Announcement
    </button>
</div>



<div class="card">
    <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <div class="d-flex gap-2 flex-wrap">
            <a href="announcements.php" class="btn btn-sm <?= $filter==='All'?'btn-dark':'btn-outline-dark' ?>">All</a>
            <?php foreach ($categories as $cat): ?>
                <a href="announcements.php?category=<?= urlencode($cat) ?>"
                   class="btn btn-sm <?= $filter===$cat?'btn-dark':'btn-outline-dark' ?>"><?= e($cat) ?></a>
            <?php endforeach; ?>
        </div>
        <input type="text" id="search" class="form-control form-control-sm" style="max-width:220px"
               placeholder="Search title..." onkeyup="searchTable()">
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0" id="dataTable">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Event Date</th>
                    <th>Posted By</th>
                    <th>Posted On</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($announcements)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No announcements posted yet.</td></tr>
                <?php else: foreach ($announcements as $a): ?>
                    <tr>
                        <td>
                            <strong><?= e($a['title']) ?></strong>
                            <div class="text-muted small"><?= e(mb_strimwidth($a['description'], 0, 90, '...')) ?></div>
                        </td>
                        <td><span class="badge text-bg-info"><?= e($a['category']) ?></span></td>
                        <td><?= $a['event_date'] ? date('M d, Y', strtotime($a['event_date'])) : '<span class="text-muted">&mdash;</span>' ?></td>
                        <td><?= e($a['admin_email']) ?></td>
                        <td><?= date('M d, Y', strtotime($a['created_at'])) ?></td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                    data-bs-toggle="modal" data-bs-target="#announcementModal"
                                    onclick='populateAnnouncementForm(<?= json_encode([
                                        'id' => $a['announcement_id'],
                                        'title' => $a['title'],
                                        'description' => $a['description'],
                                        'category' => $a['category'],
                                        'event_date' => $a['event_date'],
                                    ]) ?>)'>
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <form action="../../controllers/AnnouncementController.php" method="POST" class="d-inline"
                                  onsubmit="return confirmDelete()">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="announcement_id" value="<?= (int)$a['announcement_id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>





<!-- Add / Edit Announcement Modal -->
<div class="modal fade" id="announcementModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="../../controllers/AnnouncementController.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="announcementModalTitle">New Announcement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="announcement_id" id="announcement_id" value="">

                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" id="announcement_title" class="form-control" maxlength="150" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="announcement_description" class="form-control" rows="4" required></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-7 mb-3">
                            <label class="form-label">Category</label>
                            <select name="category" id="announcement_category" class="form-select">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= e($cat) ?>"><?= e($cat) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Determines which thematic channel the entry appears under.</div>
                        </div>
                        <div class="col-md-5 mb-3">
                            <label class="form-label">Event / Distribution Date</label>
                            <input type="date" name="event_date" id="announcement_event_date" class="form-control">
                            <div class="form-text">Optional.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save Announcement</button>
                </div>
            </form>
        </div>
    </div>
</div>




<script>
function resetAnnouncementForm() {
    document.getElementById('announcementModalTitle').textContent = 'New Announcement';
    document.getElementById('announcement_id').value = '';
    document.getElementById('announcement_title').value = '';
    document.getElementById('announcement_description').value = '';
    document.getElementById('announcement_category').value = 'General';
    document.getElementById('announcement_event_date').value = '';
}

function populateAnnouncementForm(data) {
    document.getElementById('announcementModalTitle').textContent = 'Edit Announcement';
    document.getElementById('announcement_id').value = data.id;
    document.getElementById('announcement_title').value = data.title;
    document.getElementById('announcement_description').value = data.description;
    document.getElementById('announcement_category').value = data.category;
    document.getElementById('announcement_event_date').value = data.event_date ?? '';
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
