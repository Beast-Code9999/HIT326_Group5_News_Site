<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: ../Views/User/homescreen.php');
} else {
    header('Location: ../Views/User/login.php');
}
exit;
?>
