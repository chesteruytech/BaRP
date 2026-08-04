<?php

require_once "../config/database.php";
require_once "../config/session.php";

if ($_SERVER["REQUEST_METHOD"] != "POST"){
    header("Location: ../login.php");
    exit(); 
}

$email = $_POST["email"];
$password = $_POST["password"];

if ($email == "" || $password == ""){
    header("Location: ../login.php?error=empty");
    exit();
}

$database = new Database();
$conn = $database->connect();

$sql = "SELECT * FROM users WHERE email = :email";
$stmt = $conn->prepare($sql);
$stmt->bindParam(":email", $email);
$stmt->execute();

$user = $stmt->fetch();

if (!$user || !password_verify($password, $user["password"])){
    header("Location: ../login.php?error=invalid");
    exit();
}

$_SESSION["user_id"] = $user["user_id"];
$_SESSION["email"] = $user["email"];
$_SESSION["role"] = $user["role"];

if ($user["role"] == "admin"){
    header("Location: ../views/admin/dashboard.php");
    exit();
} 
else{
    header("Location: ../views/resident/profile.php");
    exit();
}

?>
