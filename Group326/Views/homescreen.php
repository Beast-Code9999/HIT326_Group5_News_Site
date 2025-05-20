<?php
require_once '../CSS/header.php'; // Adjust path based on file structure
require '../Controller/authenticate.php';
require '../Database/db_connection.php';

// Removed the 'approved' condition from the query
$stmt = $pdo->query("SELECT title, updated_at, created_at FROM articles ORDER BY updated_at DESC LIMIT 5");
$articles = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head><title>Home</title></head>
<body>
<h1>Top 5 Recent Articles</h1>
<ul>
<?php foreach ($articles as $article): ?>
    <li>
        <strong><?= htmlspecialchars($article['title']) ?></strong><br>
        <em>Last updated: <?= htmlspecialchars($article['updated_at']) ?> | Created: <?= htmlspecialchars($article['created_at']) ?></em>
    </li>
<?php endforeach; ?>
</ul>
<a href="logout.php">Logout</a>
</body>
</html>
<?php require_once '../CSS/footer.php'; ?>