<?php

include("../../config/session.php");

if(!isLoggedIn()){

    header("Location: ../../login.php");

}

?>

<h1>Resident Dashboard</h1>

<ul>

<li><a href="profile.php">My Profile</a></li>

<li><a href="benefits.php">Benefits</a></li>

<li><a href="applications.php">Applications</a></li>

<li><a href="documents.php">Documents</a></li>

<li><a href="announcements.php">Announcements</a></li>

<li><a href="certificates.php">Certificates</a></li>

</ul>