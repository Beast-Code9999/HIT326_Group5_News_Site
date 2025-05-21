<?php
require '../../Controller/authenticate.php';
require '../../Database/db_connection.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role_id'] != 2) {
    die("Unauthorized access.");
}

if (!isset($_GET['id'])) {
    die("Article ID not provided.");
}

$article_id = $_GET['id'];

// Fetch article
$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
$stmt->execute([$article_id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    die("Article not found.");
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    <p><a href="homescreen.php">← Back to Home</a></p>
</body>
</html>
