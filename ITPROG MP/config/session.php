<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}


function isResident()
{
    return isset($_SESSION['role']) &&
           $_SESSION['role'] == "resident";
}

function isAdmin()
{
    return isset($_SESSION['role']) &&
           $_SESSION['role'] == "admin";
}


function requireResident()
{
    if (!isLoggedIn() || !isResident()) {

        header("Location: ../../login.php");
        exit();

    }
}



function requireAdmin()
{
    if (!isLoggedIn() || !isAdmin()) {

        header("Location: ../../login.php");
        exit();

    }
}
?>
