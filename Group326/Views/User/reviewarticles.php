<?php
require '../../Controller/authenticate.php';
require '../../Database/db_connection.php';
require_once '../HeaderFooter/header.php';

// Allow Editors (2) and Admins (10)
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role_id'], [2, 10])) {
    echo "<p>You do not have permission to access this page.</p>";
    exit;
}

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_article_id'])) {
    $articleId = (int)$_POST['delete_article_id'];
    $stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
    $stmt->execute([$articleId]);
    $message = "Article deleted successfully.";
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
        form { display: inline; }
        .message { color: green; margin-bottom: 1rem; }
        .actions { margin-top: 10px; }
        .delete-button { color: white; background-color: red; border: none; padding: 0.4rem 0.8rem; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>

<h1>Articles Awaiting Approval</h1>

<?php if (!empty($message)): ?>
    <p class="message"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<?php if (empty($articles)): ?>
    <p>No articles to review.</p>
<?php else: ?>
    <?php foreach ($articles as $article): ?>
        <div class="article-preview">
            <h3><a href="articledetails.php?id=<?= $article['id'] ?>"><?= htmlspecialchars($article['title']) ?></a></h3>
            <p><strong>By:</strong> <?= htmlspecialchars($article['username']) ?> | <small><?= $article['created_at'] ?></small></p>
            <p><?= htmlspecialchars(substr($article['content'], 0, 200)) ?>...</p>

            <div class="actions">
                <form method="post" onsubmit="return confirm('Are you sure you want to delete this article?');">
                    <input type="hidden" name="delete_article_id" value="<?= $article['id'] ?>">
                    <input type="submit" value="Delete" class="delete-button">
                </form>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>

<?php require_once '../HeaderFooter/footer.php'; ?>
