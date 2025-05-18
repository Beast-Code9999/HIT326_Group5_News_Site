<?php
require_once '../../CSS/header.php'; // Adjust path based on file structure
require_once '../../Database/db_connection.php';

?>

<h2>Create New User</h2>

<form action="../../Controller/create_userCon.php" method="post">
    <label for="username">Username:</label><br>
    <input type="text" name="username" required><br><br>

    <label for="email">Email:</label><br>
    <input type="email" name="email" required><br><br>

    <label for="password">Password:</label><br>
    <input type="password" name="password" required><br><br>

    <label for="role_id">Role:</label><br>
    <select name="role_id" required>
        <option value="1">Journalist</option>
        <option value="2">Editor</option>
        <option value="3">Reader</option>
    </select><br><br>

    <input type="submit" value="Create User">
</form>

<?php require_once '../../CSS/footer.php'; ?>
