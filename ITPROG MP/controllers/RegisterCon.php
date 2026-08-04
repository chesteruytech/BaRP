<?php
require_once "../config/database.php";
if ($_SERVER["REQUEST_METHOD"] != "POST"){
    header("Location: ../register.php");
    exit();
}

$first_name = trim($_POST["first_name"]);
$last_name = trim($_POST["last_name"]);
$email = trim($_POST["email"]);
$password = $_POST["password"];
$address = trim($_POST["address"]);
$contact_number = trim($_POST["contact_number"]);
$civil_status = $_POST["civil_status"];
$birthdate = $_POST["birthdate"];
$gender = $_POST["gender"];

if ($first_name == "" || $last_name == "" || $email == "" || $password == "" || $address == "" || $contact_number == "" || $civil_status == "" || $birthdate == "" || $gender == ""){
    header("Location: ../register.php?error=empty");
    exit();
}

$database = new Database();
$conn = $database->connect();

$checkSql = "SELECT user_id FROM users WHERE email = :email";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bindParam(":email", $email);
$checkStmt->execute();

if ($checkStmt->fetch()){
    header("Location: ../register.php?error=email");
    exit();
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$conn->beginTransaction();

$userSql = "INSERT INTO users (email, password, role) VALUES (:email, :password, 'resident')";
$userStmt = $conn->prepare($userSql);
$userStmt->bindParam(":email", $email);
$userStmt->bindParam(":password", $hashedPassword);
$userStmt->execute();

$user_id = $conn->lastInsertId();

$household_number = "HH-" . $user_id;

$householdSql = "INSERT INTO households (household_number, address) VALUES (:household_number, :address)";
$householdStmt = $conn->prepare($householdSql);
$householdStmt->bindParam(":household_number", $household_number);
$householdStmt->bindParam(":address", $address);
$householdStmt->execute();

$household_id = $conn->lastInsertId();

$residentSql = "INSERT INTO residents (user_id, household_id, first_name, last_name, birthdate, gender, contact_number, civil_status)
                VALUES (:user_id, :household_id, :first_name, :last_name, :birthdate, :gender, :contact_number, :civil_status)";
$residentStmt = $conn->prepare($residentSql);
$residentStmt->bindParam(":user_id", $user_id);
$residentStmt->bindParam(":household_id", $household_id);
$residentStmt->bindParam(":first_name", $first_name);
$residentStmt->bindParam(":last_name", $last_name);
$residentStmt->bindParam(":birthdate", $birthdate);
$residentStmt->bindParam(":gender", $gender);
$residentStmt->bindParam(":contact_number", $contact_number);
$residentStmt->bindParam(":civil_status", $civil_status);
$residentStmt->execute();

$conn->commit();

header("Location: ../login.php?registered=1");
exit();

?>
