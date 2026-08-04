<?php
$error = isset($_GET["error"]) ? $_GET["error"] : "";

$errorHtml = "";
if ($error == "invalid"){
    $errorHtml = "<p class='text-danger'>Invalid email or password.</p>";
} 
elseif ($error == "empty"){
    $errorHtml = "<p class='text-danger'>Please fill in all fields.</p>";
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Barangay Resident Portal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assests/css/style.css">
</head>

<body style="background-color: #f4f6f9;">

<div style="max-width: 380px; margin: 100px auto; padding: 30px; background: white; border: 1px solid #ddd; border-radius: 10px; text-align: center;">

    <h2>Barangay Resident Portal</h2>

    <?php echo $errorHtml; ?>

    <form action="controllers/LoginCon.php" method="POST" style="text-align: left;">

        <label>Email</label>
        <input type="email" name="email" class="form-control mb-3">

        <label>Password</label>
        <input type="password" name="password" class="form-control mb-3">

        <button type="submit" class="btn btn-primary w-100">Login</button>

    </form>

    <p class="mt-3">Don't have an account? <a href="register.php">Register</a></p>

</div>

</body>
</html>
