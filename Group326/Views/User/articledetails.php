<?php
require '../../Controller/authenticate.php';
require '../../Database/db_connection.php';

$article_id = $_GET['id'] ?? null;
if (!$article_id) {
    die("Article ID not provided.");
}

// Get user info
$user = $_SESSION['user'] ?? null;
$user_id = $user['id'] ?? null;
$role_id = $user['role_id'] ?? null;

$is_admin = ($role_id == 10);
$is_editor = ($role_id == 2);
$can_manage_article = $is_admin || $is_editor;

// Handle comment deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comment_id']) && $user_id) {
    $comment_id = $_POST['delete_comment_id'];

    if ($can_manage_article) {
        $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
        $stmt->execute([$comment_id]);
    } else {
        $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
        $stmt->execute([$comment_id, $user_id]);
    }
}

// Handle comment posting
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_content']) && $user_id && empty($_POST['delete_comment_id'])) {
    $comment = trim($_POST['comment_content']);
    if (!empty($comment)) {
        $stmt = $pdo->prepare("INSERT INTO comments (article_id, user_id, content, is_approved) VALUES (?, ?, ?, 1)");
        $stmt->execute([$article_id, $user_id, $comment]);
    }
}

// Handle article update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_article']) && $can_manage_article) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $allow_comments = isset($_POST['allow_comments']) ? 1 : 0;
    $is_published = isset($_POST['is_published']) ? 1 : 0;

    $stmt = $pdo->prepare("
        UPDATE articles 
        SET title = ?, content = ?, allow_comments = ?, is_published = ?, updated_at = NOW() 
        WHERE id = ?
    ");
    $stmt->execute([$title, $content, $allow_comments, $is_published, $article_id]);
    $success = "Article updated successfully.";
}

// Handle article deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_article']) && $can_manage_article) {
    $stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
    $stmt->execute([$article_id]);
    header("Location: reviewarticles.php");
    exit;
}

// Fetch article
$stmt = $pdo->prepare("
    SELECT a.*, u.username 
    FROM articles a 
    JOIN users u ON a.author_id = u.id 
    WHERE a.id = ?
");
$stmt->execute([$article_id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$article) {
    die("Article not found.");
}

// Fetch comments
$commentsStmt = $pdo->prepare("
    SELECT c.id, c.content, c.created_at, c.user_id, u.username 
    FROM comments c 
    JOIN users u ON c.user_id = u.id 
    WHERE c.article_id = ? AND c.is_approved = 1 
    ORDER BY c.created_at DESC
");
$commentsStmt->execute([$article_id]);
$comments = $commentsStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($article['title']) ?></title>
</head>
<body>

<h1><?= htmlspecialchars($article['title']) ?></h1>
<p><strong>By:</strong> <?= htmlspecialchars($article['username']) ?></p>
<p><strong>Created At:</strong> <?= $article['created_at'] ?></p>
<?php if ($article['updated_at']): ?>
    <p><strong>Updated At:</strong> <?= $article['updated_at'] ?></p>
<?php endif; ?>
<p><strong>Allow Comments:</strong> <?= $article['allow_comments'] ? 'Yes' : 'No' ?></p>
<p><strong>Published:</strong> <?= $article['is_published'] ? 'Yes' : 'No' ?></p>

<?php if (!empty($article['image_data'])): ?>
    <img src="data:image/jpeg;base64,<?= base64_encode($article['image_data']) ?>" style="max-width: 100%;"><br><br>
<?php endif; ?>

<p><?= nl2br(htmlspecialchars($article['content'])) ?></p>

<a href="homescreen.php">← Back to Home</a>

<?php if ($can_manage_article): ?>
    <hr>
    <h2>Edit Article</h2>
    <?php if (!empty($success)) echo "<p style='color: green;'>$success</p>"; ?>
    <form method="post">
        <label>Title:</label><br>
        <input type="text" name="title" value="<?= htmlspecialchars($article['title']) ?>" required><br><br>

        <label>Content:</label><br>
        <textarea name="content" rows="10" cols="80" required><?= htmlspecialchars($article['content']) ?></textarea><br><br>

        <label><input type="checkbox" name="allow_comments" <?= $article['allow_comments'] ? 'checked' : '' ?>> Allow Comments</label><br>
        <label><input type="checkbox" name="is_published" <?= $article['is_published'] ? 'checked' : '' ?>> Published</label><br><br>

        <input type="submit" name="update_article" value="Update Article">
    </form>

    <!-- Plain delete article button -->
    <form method="post" onsubmit="return confirm('Are you sure you want to delete this article?');">
        <input type="hidden" name="delete_article" value="1">
        <button type="submit">Delete Article</button>
    </form>
<?php endif; ?>

<?php if ($article['allow_comments']): ?>
    <hr>
    <h3>Comments</h3>

    <?php if ($user_id): ?>
        <form method="post">
            <textarea name="comment_content" rows="4" cols="60" placeholder="Write your comment here..." required></textarea><br>
            <button type="submit">Post Comment</button>
        </form>
    <?php else: ?>
        <p><em>You must be logged in to post a comment.</em></p>
    <?php endif; ?>

    <?php if (count($comments) > 0): ?>
        <ul>
        <?php foreach ($comments as $comment): ?>
            <li>
                <strong><?= htmlspecialchars($comment['username']) ?></strong> (<?= $comment['created_at'] ?>):<br>
                <?= nl2br(htmlspecialchars($comment['content'])) ?><br>
                <?php if ($can_manage_article || ($user_id && $user_id == $comment['user_id'])): ?>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="delete_comment_id" value="<?= $comment['id'] ?>">
                        <button type="submit" onclick="return confirm('Delete this comment?')">Delete</button>
                    </form>
                <?php endif; ?>
            </li><br>
        <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No comments yet.</p>
    <?php endif; ?>
<?php endif; ?>

</body>
</html>
