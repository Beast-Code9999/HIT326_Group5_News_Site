<?php
require_once '../HeaderFooter/header.php'; // Adjust path based on file structure
require '../../Database/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && $password === $user['password']) { // Note: Plaintext comparison (no hashing)
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username']; // Optional: keep username if you still use it elsewhere
        $_SESSION['role_id'] = $user['role_id'];
        $_SESSION['user'] = $user;

        if ($user['role_id'] == 10) {
            header('Location: ../Admin/admin_home.php');
        } else {
            header('Location: ../User/homescreen.php');
        }
        exit;
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
    Email: <input type="email" name="email" required><br>
    Password: <input type="password" name="password" required><br>
    <input type="submit" value="Login">
</form>

<p>Don't have an account? <a href="signup.php">Sign Up</a></p>

</body>
</html>
<?php require_once '../HeaderFooter/footer.php'; ?>
