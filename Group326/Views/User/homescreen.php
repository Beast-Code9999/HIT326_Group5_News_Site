<?php
require '../../Controller/authenticate.php';
require '../../Database/db_connection.php';
require_once '../HeaderFooter/header.php';

// Allow posting if user is a journalist (1), editor (2), or admin (10)
$canPost = false;
if (isset($_SESSION['user']) && in_array($_SESSION['user']['role_id'], [1, 2, 10])) {
    $canPost = true;
}

// Handle article submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canPost) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $user_id = $_SESSION['user']['id'];
    $allow_comments = isset($_POST['allow_comments']) ? 1 : 0;

    $imageData = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageTmp = $_FILES['image']['tmp_name'];
        $imageData = file_get_contents($imageTmp);
    }

    $stmt = $pdo->prepare("
        INSERT INTO articles 
        (title, content, author_id, allow_comments, image_data, created_at, is_published)
        VALUES (?, ?, ?, ?, ?, NOW(), 0)
    ");
    $stmt->bindParam(1, $title);
    $stmt->bindParam(2, $content);
    $stmt->bindParam(3, $user_id);
    $stmt->bindParam(4, $allow_comments);
    $stmt->bindParam(5, $imageData, PDO::PARAM_LOB);
    $stmt->execute();

    $message = "Article submitted successfully! Awaiting editor approval.";
}

// Fetch published articles
$stmt = $pdo->query("
    SELECT a.id, a.title, a.content, a.image_data, a.created_at, u.username 
    FROM articles a
    JOIN users u ON a.author_id = u.id
    WHERE a.is_published = 1
    ORDER BY a.created_at DESC
    LIMIT 5
");
$recentArticles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Post Article</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 2rem; background-color: #f9f9f9; }
        form, .article-preview { background: #fff; padding: 1rem; border-radius: 8px; margin-bottom: 2rem; }
        input[type="text"], textarea { width: 100%; padding: 0.5rem; margin-bottom: 1rem; }
        input[type="submit"] { padding: 0.5rem 1rem; }
        .article-preview img { max-width: 100%; height: auto; margin-bottom: 1rem; }
        .article-preview h3 { margin-bottom: 0.5rem; }
    </style>
</head>
<body>

<?php
// Show review link if user is editor or admin
if (in_array($_SESSION['user']['role_id'], [2, 10])): ?>
    <p><a href="reviewarticles.php">Review Pending Articles</a></p>
<?php endif; ?>

<h1>Recent Articles</h1>

<?php foreach ($recentArticles as $article): ?>
    <div class="article-preview">
        <?php $link = "articledetails.php?id=" . $article['id']; ?>
        <h3><a href="<?= $link ?>"><?= htmlspecialchars($article['title']) ?></a></h3>
        <p><strong>By:</strong> <?= htmlspecialchars($article['username']) ?> | <small><?= $article['created_at'] ?></small></p>

        <?php if (!empty($article['image_data'])): ?>
            <img src="data:image/jpeg;base64,<?= base64_encode($article['image_data']) ?>" alt="Article Image">
        <?php endif; ?>

        <p><?= htmlspecialchars(substr($article['content'], 0, 200)) ?>...</p>
    </div>
<?php endforeach; ?>

<hr>

<?php if ($canPost): ?>
    <h2>Submit New Article</h2>
    <?php if (!empty($message)) echo "<p style='color: green;'>$message</p>"; ?>
    <form method="post" enctype="multipart/form-data">
        <label>Title:</label>
        <input type="text" name="title" required>

        <label>Content:</label>
        <textarea name="content" rows="6" required></textarea>

        <label>Allow Comments:</label>
        <input type="checkbox" name="allow_comments"><br><br>

        <label>Upload Image:</label>
        <input type="file" name="image" accept="image/*"><br><br>

        <input type="submit" value="Submit Article">
    </form>
<?php else: ?>
    <p><em>Login as an Author, Editor, or Admin to post a new article.</em></p>
<?php endif; ?>

</body>
</html>

<?php require_once '../HeaderFooter/footer.php'; ?>
