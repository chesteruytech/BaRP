<?php
require_once __DIR__ . "/../../config/session.php";
requireResident();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Applications - Barangay Resident Portal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assests/css/style.css">
</head>

<body style="background-color: #f4f6f9;">

<div style="background: #0d6efd; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center;">

    <div>
        <b style="color: white; text-decoration: none; font-size: 20px">BaRP: Barangay Resident Portal</b>
    </div>

    <div>
        <a href="programs.php" style="color: white; margin-right: 20px; text-decoration: none;">Programs</a>
        <a href="certificates.php" style="color: white; margin-right: 20px; text-decoration: none;">Certificates</a>
        <a href="applications.php" style="color: white; margin-right: 20px; text-decoration: none;">Applications</a>
        <a href="profile.php" style="color: white; margin-right: 20px; text-decoration: none;">Profile</a>
        <a href="../../logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>

</div>

<div style="max-width: 700px; margin: 30px auto; padding: 20px; background: white; border: 1px solid #ddd; border-radius: 10px;">

    <h2>My Applications</h2>

    <p>application stuff</p>

</div>

</body>
</html>
