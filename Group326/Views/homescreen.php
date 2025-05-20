<?php
require '../Controller/authenticate.php';
require '../Database/db_connection.php';
require_once '../CSS/header.php'; // Adjust path based on file structure

// Check if the user is logged in and has author/editor access
$canPost = false;
if (isset($_SESSION['user']) && in_array($_SESSION['user']['role_id'], [1, 2])) {
    $canPost = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canPost) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $keywords = $_POST['keywords'];
    $user_id = $_SESSION['user']['id'];
    $allow_comments = isset($_POST['allow_comments']) ? 1 : 0;

    // Image upload handling
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageTmp = $_FILES['image']['tmp_name'];
        $imageName = basename($_FILES['image']['name']);
        $targetDir = "../CSS/Images/";
        $targetFile = $targetDir . time() . "_" . $imageName;
        if (move_uploaded_file($imageTmp, $targetFile)) {
            $imagePath = $targetFile;
        }
    }

    // Insert article into DB
    $stmt = $pdo->prepare("INSERT INTO articles (title, content, keywords, author_id, allow_comments, image_path, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->execute([$title, $content, $keywords, $user_id, $allow_comments, $imagePath]);

    $message = "Article submitted successfully! Awaiting editor approval.";
}

// Fetch 5 most recent articles with author info
$stmt = $pdo->query("
    SELECT a.title, a.content, a.image_path, a.created_at, u.username 
    FROM articles a
    JOIN users u ON a.author_id = u.id
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

<h1>Recent Articles</h1>

<?php foreach ($recentArticles as $article): ?>
    <div class="article-preview">
        <h3><?= htmlspecialchars($article['title']) ?></h3>
        <p><strong>By:</strong> <?= htmlspecialchars($article['username']) ?> | <small><?= $article['created_at'] ?></small></p>
        <?php if (!empty($article['image_path'])): ?>
            <img src="<?= htmlspecialchars($article['image_path']) ?>" alt="Article Image">
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

        <label>Keywords:</label>
        <input type="text" name="keywords">

        <label>Allow Comments:</label>
        <input type="checkbox" name="allow_comments"><br><br>

        <label>Upload Image:</label>
        <input type="file" name="image"><br><br>

        <input type="submit" value="Submit Article">
    </form>
<?php else: ?>
    <p><em>Login as an Author or Editor to post a new article.</em></p>
<?php endif; ?>

<a href="homescreen.php">← Back to Home</a>

</body>
</html>