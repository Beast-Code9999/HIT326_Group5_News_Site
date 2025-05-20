<?php
require_once '../HeaderFooter/header.php'; // Adjust path based on file structure
session_start();
session_destroy();
header('Location: login.php');
exit;
?>
<?php require_once '../HeaderFooter/footer.php'; ?>
