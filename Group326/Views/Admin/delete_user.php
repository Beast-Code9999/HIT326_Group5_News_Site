////////////////////Faulty

<?php
require_once '../../Database/db_connection.php';
session_start();

// Only allow Admins
if (!isset($_SESSION['user']) || $_SESSION['user']['role_id'] != 10) {
    die("Access denied. Admins only.");
}

// Check if a valid user ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid user ID.");
}

$userId = (int) $_GET['id'];

// Prevent admin from deleting their own account
if ($_SESSION['user']['id'] == $userId) {
    die("You cannot delete your own account.");
}

try {
    // First, check if the user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        die("User not found.");
    }

    // Attempt to delete the user
    $deleteStmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $deleteStmt->execute([$userId]);

    echo "User deleted successfully. <a href='user_list.php'>Back to user list</a>";
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        // Foreign key constraint error
        echo "Error: Cannot delete user due to existing related records (e.g., articles authored).";
    } else {
        echo "Error deleting user: " . $e->getMessage();
    }
}
?>
