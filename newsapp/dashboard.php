<?php
session_start();
if (!isset($_SESSION['journalist_id'])) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Austro-Asian Times</title>
</head>
<body>
    <h2>Welcome, Journalist!</h2>
    <p>You have successfully logged in.</p>
    <p><a href="logout.php">Logout</a></p>
</body>
</html>