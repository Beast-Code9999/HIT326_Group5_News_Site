<?php
require '../../Controller/authenticate.php';
require '../../Database/db_connection.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role_id'] != 2) {
    die("Unauthorized access.");
}

$article_id = $_GET['id'] ?? null;
if (!$article_id) {
    die("Article ID not provided.");
}

// Handle comment deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comment_id'])) {
    $comment_id = $_POST['delete_comment_id'];
    $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
    $stmt->execute([$comment_id]);
}

// Fetch article
$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
$stmt->execute([$article_id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$article) {
    die("Article not found.");
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_comment_id'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $allow_comments = isset($_POST['allow_comments']) ? 1 : 0;
    $is_published = isset($_POST['is_published']) ? 1 : 0;

    $stmt = $pdo->prepare("
        UPDATE articles SET 
            title = ?, 
            content = ?, 
            allow_comments = ?, 
            is_published = ?, 
            updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$title, $content, $allow_comments, $is_published, $article_id]);

    $success = "Article updated successfully.";
    // Reload updated article
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([$article_id]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch comments for the article
$commentsStmt = $pdo->prepare("
    SELECT c.id, c.content, c.created_at, u.username 
    FROM comments c 
    JOIN users u ON c.user_id = u.id 
    WHERE c.article_id = ? 
    ORDER BY c.created_at DESC
");
$commentsStmt->execute([$article_id]);
$comments = $commentsStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Article</title>
</head>
<body>
    <h1>Edit Article</h1>

    <?php if (!empty($success)) echo "<p style='color: green;'>$success</p>"; ?>

    <form method="post">
        <label>Title:</label><br>
        <input type="text" name="title" value="<?= htmlspecialchars($article['title']) ?>" required><br><br>

        <label>Content:</label><br>
        <textarea name="content" rows="10" cols="80" required><?= htmlspecialchars($article['content']) ?></textarea><br><br>

        <label>Allow Comments:</label>
        <input type="checkbox" name="allow_comments" <?= $article['allow_comments'] ? 'checked' : '' ?>><br><br>

        <label>Published:</label>
        <input type="checkbox" name="is_published" <?= $article['is_published'] ? 'checked' : '' ?>><br><br>

        <input type="submit" value="Update Article">
    </form>

    <h3>Comments</h3>
    <?php if (count($comments) > 0): ?>
        <ul>
            <?php foreach ($comments as $comment): ?>
                <li>
                    <strong><?= htmlspecialchars($comment['username']) ?></strong> (<?= $comment['created_at'] ?>):<br>
                    <?= nl2br(htmlspecialchars($comment['content'])) ?><br>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="delete_comment_id" value="<?= $comment['id'] ?>">
                        <button type="submit" onclick="return confirm('Delete this comment?')">Delete</button>
                    </form>
                </li><br>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No comments yet.</p>
    <?php endif; ?>

    <p><a href="homescreen.php">← Back to Home</a></p>
</body>
</html>
