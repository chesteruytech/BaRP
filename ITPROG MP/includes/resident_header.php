<?php
$pageTitle = $pageTitle ?? 'Resident Portal';
$activePage = $activePage ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> - Barangay Resident Portal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <a class="navbar-brand" href="announcements.php">Barangay Resident Portal</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#residentNav"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="residentNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link <?= $activePage==='announcements'?'active':'' ?>" href="announcements.php">Announcements</a></li>
        <li class="nav-item"><a class="nav-link <?= $activePage==='profile'?'active':'' ?>" href="profile.php">Profile</a></li>
        <li class="nav-item"><a class="nav-link <?= $activePage==='programs'?'active':'' ?>" href="programs.php">Programs</a></li>
        <li class="nav-item"><a class="nav-link <?= $activePage==='applications'?'active':'' ?>" href="applications.php">Applications</a></li>
        <li class="nav-item"><a class="nav-link <?= $activePage==='documents'?'active':'' ?>" href="documents.php">Documents</a></li>
        <li class="nav-item"><a class="nav-link <?= $activePage==='certificates'?'active':'' ?>" href="certificates.php">Certificates</a></li>
      </ul>
      <a href="../../logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
  </div>
</nav>
<main class="container py-4">
<?= portalFlashFromQuery() ?>
