<!DOCTYPE html>
<html>
<head>
    <title>Barangay Resident Portal</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<h2>Barangay Resident Portal</h2>

<form action="controllers/LoginController.php" method="POST">

    <input type="email" name="email" placeholder="Email">

    <input type="password" name="password" placeholder="Password">

    <button type="submit">Login</button>

</form>

<a href="register.php">Register</a>

</body>
</html>