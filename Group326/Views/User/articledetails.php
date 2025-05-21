<?php
require '../../Controller/authenticate.php';
require '../../Database/db_connection.php';

if (!isset($_GET['id'])) {
    die("Article ID not provided.");
}

$article_id = $_GET['id'];

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

</body>
</html>
