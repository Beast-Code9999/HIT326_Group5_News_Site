<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: ../Views/homescreen.php');
} else {
    header('Location: ../Views/login.php');
}
exit;
?>
