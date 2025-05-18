<?php
require_once '../../CSS/header.php'; // Adjust path based on file structure
require_once '../../Database/db_connection.php';


// Check if user is logged in and is an admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role_id'] != 10) {
    die("Access denied. This page is for admins only.");
}

// Optional: fetch admin data
$adminName = htmlspecialchars($_SESSION['user']['username']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body { font-family: Arial; padding: 2rem; background: #f7f7f7; }
        .container { background: white; padding: 2rem; border-radius: 8px; max-width: 600px; margin: auto; }
        h1 { color: #333; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome Admin, <?= $adminName ?>!</h1>
        <p>This page is only accessible to users with role_id = 10.</p>
        
        <ul>
            <li><a href="create_user.php">Create User</a></li>
            <li><a href="user_list.php">View All Users</a></li>
        </ul>
    </div>
</body>
</html>
<?php require_once '../../CSS/footer.php'; ?>
