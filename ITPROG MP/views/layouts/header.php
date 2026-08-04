<?php
/**
 * Admin header
 */
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$pageTitle  = $pageTitle  ?? 'Admin';
$activePage = $activePage ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?> - Barangay Admin</title>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="../../assests/css/style.css">
<link rel="stylesheet" href="../../assests/css/admin.css">
</head>
<body>

<nav class="navbar navbar-expand navbar-dark sticky-top px-3 admin-topbar">
    <span class="navbar-brand mb-0 h1">
        <i class="bi bi-buildings"></i> Barangay Admin Panel
    </span>

    <div class="ms-auto d-flex align-items-center gap-3">
        <span class="text-white d-none d-sm-inline">
            <i class="bi bi-person-circle"></i>
            <?= htmlspecialchars($_SESSION['email'] ?? 'Admin') ?>
        </span>
        <a href="../../logout.php" class="btn btn-sm btn-outline-light" onclick="return logout()">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </div>
</nav>
