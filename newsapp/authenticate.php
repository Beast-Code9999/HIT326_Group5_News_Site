<?php
session_start();
include 'db_connection.php'; // Include your database connection script

// Check if form data is received
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare and execute the query
    $sql = "SELECT id, name, password FROM journalists WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        
        // Verify password (Plain text for now as per your request)
        if ($row['password'] === $password) {
            // Set session variables
            $_SESSION['journalist_id'] = $row['id'];
            $_SESSION['journalist_name'] = $row['name'];
            
            // Redirect to dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "No account found with that email.";
    }
} else {
    echo "Invalid Request.";
}
?>
