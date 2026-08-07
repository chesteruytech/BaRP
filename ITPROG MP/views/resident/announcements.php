<?php
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../models/Announcement.php';
requireResident();
$db=portalDb();

$category=$_GET['category']??'All';
$announcements=Announcement::all($db,$category);

$pageTitle='Announcements'; 
$activePage='announcements';

include __DIR__.'/../../includes/resident_header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h2 class="mb-1">Announcements & Events</h2>
        <p class="text-muted mb-0">Current barangay programs, schedules, and community notices.</p>
    </div>
    
    <div class="btn-group flex-wrap">
        <?php foreach(['All','General','Senior','Student','PWD','Solo Parent','Indigent'] as $c): ?>
            <a class="btn btn-sm <?= $category===$c?'btn-primary':'btn-outline-primary' ?>" href="?category=<?= urlencode($c) ?>">
                <?= e($c) ?></a>
                <?php endforeach ?>
    </div>
</div>

<?php if(!$announcements): ?>
<div class="alert alert-light border">No announcements in this category.</div>
<?php endif; ?>

<div class="row g-3">
    <?php foreach($announcements as $a): ?>

    <div class="col-md-6">
        <article class="card h-100">    
            <div class="card-body">

                <div class="d-flex justify-content-between gap-2">
                    <span class="badge text-bg-primary"><?= e($a['category']) ?></span>
                        <?php if($a['event_date']): ?>
                    <span class="text-muted small">
                        <i class="bi bi-calendar-event"></i> <?= e(date('M d, Y',strtotime($a['event_date']))) ?>
                    </span>
                
                    <?php endif ?>
                </div>
            
                <h4 class="mt-3"><?= e($a['title']) ?></h4>
                <p class="mb-2"><?= nl2br(e($a['description'])) ?></p>
                <small class="text-muted">Posted <?= e(date('M d, Y',strtotime($a['created_at']))) ?></small>
            </div>
        </article>
    </div>
    <?php endforeach ?>
</div>

<?php include __DIR__.'/../../includes/resident_footer.php'; ?>
