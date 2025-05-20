<?php
require_once '../CSS/header.php'; // Adjust path based on file structure
require_once '../Database/db_connection.php';

// Allow only Admins to create users
if (!isset($_SESSION['user']) || $_SESSION['user']['role_id'] != 10) {
    die("Access denied. Only admins can create users.");
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role_id = (int) $_POST['role_id'];

    // Basic validation
    if (!$username || !$email || !$password || !$role_id) {
        die("All fields are required.");
    }

    try {
        // Optional: check if username or email already exists
        $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
        $check->execute([$username, $email]);
        if ($check->fetchColumn() > 0) {
            die("Username or email already exists.");
        }

        // Insert new user
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $email, $password, $role_id]);

        echo "User created successfully. <a href='../Views/Admin/user_list.php'>Back to user list</a>";
    } catch (PDOException $e) {
        echo "Error creating user: " . $e->getMessage();
    }
} else {
    echo "Invalid request.";
}
?>
<?php require_once '../CSS/footer.php'; ?>
