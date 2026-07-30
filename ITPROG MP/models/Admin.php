<?php

include("../../config/session.php");

if(!isAdmin()){

    header("Location: ../../login.php");

}

?>

<h1>Admin Dashboard</h1>

<ul>

<li>Manage Residents</li>

<li>Manage Announcements</li>

<li>Manage Programs</li>

<li>Requirements Builder</li>

<li>Applications</li>

<li>Certificate Requests</li>

</ul>