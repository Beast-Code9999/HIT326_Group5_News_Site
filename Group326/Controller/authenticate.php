<?php
session_start();
require '../Database/db_connection.php'; // Assumes $pdo is defined in this file

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT id, username, password_hash, role_id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Compare directly as plain text
    if ($user && $password === $user['password']) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role_id'] = $user['role_id'];

        header("Location: ../Views/homescreen.php");
        exit;
    } else {
        echo "Invalid username or password.";
    }
} else {
    echo "Invalid request method.";
}
