<?php
require_once '../HeaderFooter/header.php'; // Adjust path based on file structure
require '../../Database/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && $password === $user['password']) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role_id'] = $user['role_id'];

        // Redirect based on role
        if ($user && $password === $user['password']) {
            $_SESSION['user'] = $user;

            if ($user['role_id'] == 10) {
                header('Location: ../Admin/admin_home.php');
            } else {
                header('Location: ../User/homescreen.php');
            }
            exit;
}
    } else {
        $error = "Invalid login credentials.";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Login</title></head>
<body>
<h2>Login</h2>
<?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
<form method="post">
    Username: <input type="text" name="username" required><br>
    Password: <input type="password" name="password" required><br>
    <input type="submit" value="Login">
</form>
</body>
</html>
<?php require_once '../HeaderFooter/footer.php'; ?>

