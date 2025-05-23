<?php
require '../../Controller/authenticate.php';
require '../../Database/db_connection.php';
require_once '../HeaderFooter/header.php';

// Allow Editors (2) and Admins (10)
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role_id'], [2, 10])) {
    echo "<p>You do not have permission to access this page.</p>";
    exit;
}

// Fetch all unpublished articles
$stmt = $pdo->query("
    SELECT a.id, a.title, a.content, a.created_at, u.username 
    FROM articles a
    JOIN users u ON a.author_id = u.id
    WHERE a.is_published = 0
    ORDER BY a.created_at DESC
");
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Review Articles</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 2rem; background-color: #f4f4f4; }
        .article-preview { background: #fff; padding: 1rem; margin-bottom: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        a { color: #007BFF; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<h1>Articles Awaiting Approval</h1>

<?php if (empty($articles)): ?>
    <p>No articles to review.</p>
<?php else: ?>
    <?php foreach ($articles as $article): ?>
        <div class="article-preview">
            <h3><a href="articledetails.php?id=<?= $article['id'] ?>"><?= htmlspecialchars($article['title']) ?></a></h3>
            <p><strong>By:</strong> <?= htmlspecialchars($article['username']) ?> | <small><?= $article['created_at'] ?></small></p>
            <p><?= htmlspecialchars(substr($article['content'], 0, 200)) ?>...</p>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>

<?php require_once '../HeaderFooter/footer.php'; ?>
