<?php
require '../Controller/authenticator.php';
require '../Database/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $keywords = $_POST['keywords'];
    $user_id = $_SESSION['user_id'];
    $allow_comments = isset($_POST['allow_comments']) ? 1 : 0;
    
    // Image upload handling
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageTmp = $_FILES['image']['tmp_name'];
        $imageName = basename($_FILES['image']['name']);
        $targetDir = "uploads/";
        $targetFile = $targetDir . time() . "_" . $imageName;
        if (move_uploaded_file($imageTmp, $targetFile)) {
            $imagePath = $targetFile;
        }
    }

    $stmt = $pdo->prepare("INSERT INTO articles (title, content, keywords, author_id, allow_comments, image_path, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->execute([$title, $content, $keywords, $user_id, $allow_comments, $imagePath]);
    echo "Article submitted successfully! Awaiting editor approval.";
}
?>
<!DOCTYPE html>
<html>
<head><title>Post Article</title></head>
<body>
<h2>Post New Article</h2>
<form method="post" enctype="multipart/form-data">
    Title: <input type="text" name="title" required><br>
    Content:<br><textarea name="content" required></textarea><br>
    Keywords: <input type="text" name="keywords"><br>
    Allow Comments: <input type="checkbox" name="allow_comments"><br>
    Upload Image: <input type="file" name="image"><br>
    <input type="submit" value="Submit">
</form>
<a href="homescreen.php">Back to Home</a>
</body>
</html>
