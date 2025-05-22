<?php
require '../../Database/db_connection.php';

$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role_id = 3; // Regular user

    // Basic validation
    if (empty($username) || empty($password)) {
        $errors[] = "Username and password are required.";
    }

    // Check for existing username
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        $errors[] = "Username already exists.";
    }

    if (empty($errors)) {
        // Insert new user
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role_id) VALUES (?, ?, ?)");
        $stmt->execute([$username, $password, $role_id]);

        $success = "Account created successfully. <a href='login.php'>Log in here</a>.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sign Up</title>
</head>
<body>
    <h1>Create an Account</h1>

    <?php if (!empty($errors)): ?>
        <ul style="color:red;">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php if ($success): ?>
        <p style="color:green;"><?= $success ?></p>
    <?php else: ?>
        <form method="post">
            <label>Username:</label><br>
            <input type="text" name="username" required><br><br>

            <label>Password:</label><br>
            <input type="password" name="password" required><br><br>

            <input type="submit" value="Sign Up">
        </form>
        <p>Already have an account? <a href="login.php">Log in</a></p>
    <?php endif; ?>
</body>
</html>
