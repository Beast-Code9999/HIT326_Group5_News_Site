<?php
require_once '../HeaderFooter/header.php'; // Adjust path based on file structure
require_once '../../Database/db_connection.php';

// Check if user is admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role_id'] != 10) {
    die("Access denied. Admins only.");
}

if (!isset($_GET['id'])) {
    die("User ID is missing.");
}

$userId = (int)$_GET['id'];

// Fetch user by ID
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}

$roleNames = [
    1 => 'Author',
    2 => 'Editor',
    3 => 'User',
    10 => 'Admin'
];

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role_id = (int)$_POST['role_id'];
    $newPassword = trim($_POST['password']);

    if (empty($username) || empty($email)) {
        $error = "Username and Email are required.";
    } else {
        if (!empty($newPassword)) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE users SET username = ?, email = ?, password = ?, role_id = ? WHERE id = ?");
            $update->execute([$username, $email, $hashedPassword, $role_id, $userId]);
        } else {
            // Don't update password if not entered
            $update = $pdo->prepare("UPDATE users SET username = ?, email = ?, role_id = ? WHERE id = ?");
            $update->execute([$username, $email, $role_id, $userId]);
        }

        $success = "User details updated successfully.";

        // Refresh user data
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<h2>Update User Details</h2>

<?php if ($error): ?>
    <p style="color: red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if ($success): ?>
    <p style="color: green;"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<form method="post">
    <label>Username:</label><br>
    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required><br><br>

    <label>Email:</label><br>
    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required><br><br>

    <label>New Password (leave blank to keep current):</label><br>
    <input type="text" name="password" placeholder="Enter new password"><br><br>

    <label>Role:</label><br>
    <select name="role_id">
        <?php foreach ($roleNames as $id => $name): ?>
            <option value="<?= $id ?>" <?= $id == $user['role_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($name) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <input type="submit" value="Update User">
</form>

<br>
<a href="user_list.php">← Back to User List</a>

<?php require_once '../HeaderFooter/footer.php'; ?>

