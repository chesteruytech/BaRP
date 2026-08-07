<?php $activePage = $activePage ?? ''; ?>
<aside class="sidebar">
    <h4>Admin Panel</h4>
    <?php
    $links = [
        'dashboard' => ['dashboard.php','bi-speedometer2','Dashboard'],
        'residents' => ['residents.php','bi-people','Residents'],
        'registry' => ['registry.php','bi-person-vcard','Resident Registry'],
        'announcements' => ['announcements.php','bi-megaphone','Announcements'],
        'programs' => ['programs.php','bi-gift','Programs'],
        'requirements' => ['requirements.php','bi-list-check','Requirements Builder'],
        'applications' => ['applications.php','bi-file-earmark-text','Applications'],
        'certificates' => ['certificates.php','bi-award','Certificate Requests'],
        'documents' => ['documents.php','bi-folder2-open','Resident Documents'],
        'profile' => ['profile.php','bi-person-gear','My Profile'],
    ];
    
    foreach ($links as $key => [$href,$icon,$label]): ?>
        <a class="<?= $activePage === $key ? 'active' : '' ?>" href="<?= e($href) ?>">
            <i class="bi <?= e($icon) ?> me-2"></i><?= e($label) ?>
        </a>
    <?php endforeach; ?>
</aside>
<div class="main-content">
<?= portalFlashFromQuery() ?>
