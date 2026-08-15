<?php
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../register.php');
    exit;
}

$fields = ['first_name','last_name','email','password','address','contact_number','civil_status','birthdate','gender'];
foreach ($fields as $field) {
    if (!isset($_POST[$field]) || trim((string)$_POST[$field]) === '') {
        header('Location: ../register.php?error=empty');
        exit;
    }
}

$firstName = trim($_POST['first_name']);
$lastName = trim($_POST['last_name']);
$email = trim($_POST['email']);
$password = $_POST['password'];
$address = trim($_POST['address']);
$contact = trim($_POST['contact_number']);
$civil = $_POST['civil_status'];
$birthdate = $_POST['birthdate'];
$gender = $_POST['gender'];

$db = (new Database())->connect();
$stmt = $db->prepare('SELECT user_id FROM users WHERE email=?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    header('Location: ../register.php?error=email');
    exit;
}

try {
    $db->beginTransaction();

    $registry = null;
    try {
        $stmt = $db->prepare("SELECT * FROM resident_registry
                              WHERE LOWER(TRIM(first_name))=LOWER(TRIM(?))
                                AND LOWER(TRIM(last_name))=LOWER(TRIM(?))
                                AND birthdate=?
                                AND LOWER(TRIM(address))=LOWER(TRIM(?))
                              LIMIT 1");
        $stmt->execute([$firstName,$lastName,$birthdate,$address]);
        $registry = $stmt->fetch() ?: null;
    } catch (Throwable $ignored) {
        $registry = null;
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO users(email,password,role) VALUES(?,?,'resident')");
    $stmt->execute([$email,$hashed]);
    $userId = (int)$db->lastInsertId();

    if ($registry) {
        $stmt = $db->prepare('SELECT household_id FROM households WHERE household_number=? LIMIT 1');
        $stmt->execute([$registry['household_number']]);
        $householdId = (int)($stmt->fetchColumn() ?: 0);
        if (!$householdId) {
            $stmt = $db->prepare('INSERT INTO households(household_number,address,income_level) VALUES(?,?,?)');
            $stmt->execute([$registry['household_number'],$registry['address'],$registry['income_level']]);
            $householdId = (int)$db->lastInsertId();
        }
        $stmt = $db->prepare("INSERT INTO residents
            (user_id,household_id,first_name,last_name,birthdate,gender,contact_number,civil_status,sector,verified,registry_id)
            VALUES(?,?,?,?,?,?,?,?,?,1,?)");
        $stmt->execute([$userId,$householdId,$firstName,$lastName,$birthdate,$gender,$contact,$civil,$registry['sector'],$registry['registry_id']]);
    } else {
        $householdNumber = 'HH-' . $userId;
        $stmt = $db->prepare('INSERT INTO households(household_number,address) VALUES(?,?)');
        $stmt->execute([$householdNumber,$address]);
        $householdId = (int)$db->lastInsertId();
        $stmt = $db->prepare("INSERT INTO residents
            (user_id,household_id,first_name,last_name,birthdate,gender,contact_number,civil_status,sector,verified)
            VALUES(?,?,?,?,?,?,?,?, 'General',0)");
        $stmt->execute([$userId,$householdId,$firstName,$lastName,$birthdate,$gender,$contact,$civil]);
    }

    $db->commit();
    header('Location: ../login.php?registered=1');
    exit;
} catch (Throwable $e) {
    if ($db->inTransaction()) $db->rollBack();
    header('Location: ../register.php?error=empty');
    exit;
}
