<?php
require_once '../../CSS/header.php'; // Adjust path based on file structure
require_once '../../Database/db_connection.php';


// Check if the user is logged in and is an admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role_id'] != 10) {
    die("Access denied. This page is for admins only.");
}

// Define role mapping
$roleNames = [
    1 => 'Author',
    2 => 'Editor',
    3 => 'User',
    10 => 'Admin' // optional if needed
];

// Fetch all users from the database
$stmt = $pdo->query("SELECT id, username, email, role_id FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>User List (Admin)</title>
    <style>
        body { font-family: Arial; padding: 2rem; background: #f0f0f0; }
        table { width: 100%; border-collapse: collapse; background: white; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: left; }
        th { background-color: #eee; }
        h2 { margin-bottom: 1rem; }
        .btn-delete, .btn-update {
            padding: 5px 10px;
            text-decoration: none;
            margin-right: 5px;
        }
        .btn-delete { color: red; }
        .btn-update { color: blue; }
    </style>
</head>
<body>
    <h2>Admin Dashboard – User List</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user['id']) ?></td>
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= $roleNames[$user['role_id']] ?? 'Unknown' ?></td>
                <td>
                    <?php if ($_SESSION['user']['id'] != $user['id']): ?>
                        <a class="btn-update" href="update_user.php?id=<?= $user['id'] ?>">Update</a>
                        <a class="btn-delete" href="delete_user.php?id=<?= $user['id'] ?>" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                    <?php else: ?>
                        (You)
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
<?php require_once '../../CSS/footer.php'; ?>
