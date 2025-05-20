<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Ensure the session is started
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>My News Site</title>
    <link rel="stylesheet" href="/HIT326_Group5_News_Site/Assets/style.css"> <!-- adjust path -->
</head>
<body>
<header>
    <h1>Welcome to Austro-Asian Times</h1>
    <?php if (isset($_SESSION['user'])): ?>
        <p>Hello, <?= htmlspecialchars($_SESSION['user']['username']) ?>!</p>
        
         <?php if ($_SESSION['user']['role_id'] == 10): ?>
            <a href="../Admin/admin_home.php">Admin Home</a> |
        <?php endif; ?>
        
        <?php
        // Check if user is logged in and has role_id 1 or 2, or allow access for everyone (including non-logged-in users)
        if (!isset($_SESSION['user']['role_id']) || in_array($_SESSION['user']['role_id'], [1, 2])): ?>
            <a href="../User/homescreen.php">Home</a> |
        <?php endif; ?>

        <a href="../User/logout.php">Logout</a>
    <?php else: ?>
        <a href="../User/login.php">Login</a>
    
        <a href="../User/homescreen.php">Home</a>
    <?php endif; ?>
</header>
<hr>
