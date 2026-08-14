<?php

require_once __DIR__ . "/../../config/session.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../includes/helpers.php";

requireResident();

$pageTitle = 'My Profile';
$activePage = 'profile';

$database = new Database();
$conn = $database->connect();

$sql = "SELECT r.resident_id, r.first_name, r.middle_name, r.last_name, r.birthdate, r.gender,
               r.civil_status, r.contact_number, r.sector, r.verified,
               h.household_number, h.address, h.income_level
        FROM residents r
        JOIN households h ON h.household_id = r.household_id
        WHERE r.user_id = :user_id";

$stmt = $conn->prepare($sql);
$stmt->bindParam(":user_id", $_SESSION["user_id"]);
$stmt->execute();

$resident = $stmt->fetch();

$profileHtml = "";

if (!$resident){
    $profileHtml = "<p>No resident record found for this account.</p>";
} else {
    $fullName = htmlspecialchars($resident["first_name"] . " " . $resident["middle_name"] . " " . $resident["last_name"]);
    $birthdate = htmlspecialchars($resident["birthdate"]);
    $gender = htmlspecialchars($resident["gender"]);
    $civilStatus = htmlspecialchars($resident["civil_status"]);
    $contactNumber = htmlspecialchars($resident["contact_number"]);
    $sector = htmlspecialchars($resident["sector"]);
    $verified = $resident["verified"] ? "Yes" : "No";
    $householdNumber = htmlspecialchars($resident["household_number"]);
    $address = htmlspecialchars($resident["address"]);
    $incomeLevel = htmlspecialchars($resident["income_level"]);

    $profileHtml = "
    <table class='table table-bordered'>
        <tr><th>Full Name</th><td>$fullName</td></tr>
        <tr><th>Birthdate</th><td>$birthdate</td></tr>
        <tr><th>Gender</th><td>$gender</td></tr>
        <tr><th>Civil Status</th><td>$civilStatus</td></tr>
        <tr><th>Contact Number</th><td>$contactNumber</td></tr>
        <tr><th>Sector</th><td>$sector</td></tr>
        <tr><th>Verified</th><td>$verified</td></tr>
        <tr><th>Household Number</th><td>$householdNumber</td></tr>
        <tr><th>Address</th><td>$address</td></tr>
        <tr><th>Income Level</th><td>$incomeLevel</td></tr>
    </table>
    ";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$resident) {
        die('No resident record found; cannot upload documents.');
    }
    try {
        $uploadedDocuments = uploadDocument($conn, $resident['resident_id']);
    } catch (Throwable $e) {
        die($e->getMessage());
    }
}

$documentHtml = "
    <form action='' method='post' enctype='multipart/form-data'>
        <table class='table table-bordered'>
            <tr>
                <th>Valid Barangay or Government ID</th>
                <td><input type='file' name='validID' onchange='this.form.submit()'></td>
            </tr>
            <tr>
                <th>Senior Citizen ID</th>
                <td><input type='file' name='senior' onchange='this.form.submit()'></td>
            </tr>
            <tr>
                <th>Proof of Low Income or Indigency</th>
                <td><input type='file' name='indigent' onchange='this.form.submit()'></td>
            </tr>
            <tr>
                <th>School ID or Proof of Enrollment</th>
                <td><input type='file' name='student' onchange='this.form.submit()'></td>
            </tr>
        </table>
    </form>
    ";
?>

<?php include __DIR__ . '/../../includes/resident_header.php'; ?>

<div style="max-width: 700px; margin: 30px auto; padding: 20px; background: white; border: 1px solid #ddd; border-radius: 10px;">

    <h2>My Profile</h2>

    <?= $profileHtml; ?>

</div>

<div style="max-width: 700px; margin: 30px auto; padding: 20px; background: white; border: 1px solid #ddd; border-radius: 10px;"> <!--Document Dashboard-->

    <h2>Documents</h2>

    <?= $documentHtml; ?>

</div>

<?php include __DIR__ . '/../../includes/resident_footer.php'; ?>
