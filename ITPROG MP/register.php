<?php
$error = isset($_GET["error"]) ? $_GET["error"] : "";

$errorHtml = "";
if ($error == "email"){
    $errorHtml = "<p class='text-danger'>That email is already registered.</p>";
}
elseif ($error == "empty"){
    $errorHtml = "<p class='text-danger'>Please fill in all fields.</p>";
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Register - Barangay Resident Portal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assests/css/style.css">
</head>

<body style="background-color: #f4f6f9;">

<div style="max-width: 380px; margin: 60px auto; padding: 30px; background: white; border: 1px solid #ddd; border-radius: 10px; text-align: center;">

    <h2>Register</h2>

    <?php echo $errorHtml; ?>

    <form action="controllers/RegisterCon.php" method="POST" style="text-align: left;">

        <label>First Name</label>
        <input type="text" name="first_name" class="form-control mb-3">

        <label>Last Name</label>
        <input type="text" name="last_name" class="form-control mb-3">

        <label>Email</label>
        <input type="email" name="email" class="form-control mb-3">

        <label>Password</label>
        <input type="password" name="password" class="form-control mb-3">

        <label>Street Address</label>
        <input type="text" name="address" class="form-control mb-3">

        <label>Contact Number</label>
        <input type="text" name="contact_number" class="form-control mb-3">

        <label>Civil Status</label>
        <select name="civil_status" class="form-select mb-3">
            <option value="">Select Civil Status</option>
            <option value="Single">Single</option>
            <option value="Married">Married</option>
            <option value="Widowed">Widowed</option>
            <option value="Separated">Separated</option>
        </select>

        <label>Date of Birth</label>
        <input type="date" name="birthdate" class="form-control mb-3">

        <label>Gender</label>
        <select name="gender" class="form-select mb-3">
            <option value="">Select Gender</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
        </select>

        <button type="submit" class="btn btn-primary w-100">Register</button>

    </form>

    <p class="mt-3">Already have an account? <a href="login.php">Login</a></p>

</div>

</body>
</html>
