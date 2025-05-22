<?php
require '../../Controller/authenticate.php';
require '../../Database/db_connection.php';

if (!isset($_GET['id'])) {
    die("Article ID not provided.");
}

$article_id = $_GET['id'];
$user_id = $_SESSION['user_id'] ?? null;

// Handle comment deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comment_id']) && $user_id) {
    $comment_id = $_POST['delete_comment_id'];

    // Make sure user owns the comment
    $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
    $stmt->execute([$comment_id, $user_id]);
}

// Handle new comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_content']) && $user_id && empty($_POST['delete_comment_id'])) {
    $commentContent = trim($_POST['comment_content']);
    if (!empty($commentContent)) {
        $stmt = $pdo->prepare("INSERT INTO comments (article_id, user_id, content, is_approved) VALUES (?, ?, ?, 1)");
        $stmt->execute([$article_id, $user_id, $commentContent]);
    }
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

// Fetch approved comments
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
<p><strong>Author ID:</strong> <?= $article['author_id'] ?></p>

<?php if (!empty($article['image_data'])): ?>
    <img src="data:image/jpeg;base64,<?= base64_encode($article['image_data']) ?>" alt="Article Image" style="max-width: 100%;"><br><br>
<?php endif; ?>

<p><?= nl2br(htmlspecialchars($article['content'])) ?></p>

<a href="homescreen.php">← Back to Home</a>

<?php if ($article['allow_comments']): ?>
    <hr>
    <h3>Comments</h3>

    <?php if ($user_id): ?>
        <!-- Comment submission -->
        <form method="post">
            <textarea name="comment_content" rows="4" cols="60" placeholder="Write your comment here..." required></textarea><br>
            <button type="submit">Post Comment</button>
        </form>
    <?php else: ?>
        <p><em>You must be logged in to post a comment.</em></p>
    <?php endif; ?>

    <h4>Approved Comments:</h4>
    <?php if (count($comments) > 0): ?>
        <ul>
        <?php foreach ($comments as $comment): ?>
            <li>
                <strong><?= htmlspecialchars($comment['username']) ?></strong> (<?= $comment['created_at'] ?>):<br>
                <?= nl2br(htmlspecialchars($comment['content'])) ?>

                <!-- Show delete button if the comment belongs to the logged-in user -->
                <?php if ($user_id && $user_id == $comment['user_id']): ?>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="delete_comment_id" value="<?= $comment['id'] ?>">
                        <button type="submit" onclick="return confirm('Are you sure you want to delete this comment?')">Delete</button>
                    </form>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No comments yet.</p>
    <?php endif; ?>
<?php endif; ?>

</body>
</html>
