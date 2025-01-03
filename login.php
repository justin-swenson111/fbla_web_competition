<?php
session_start();

// Database connection details
$servername = "localhost";
$username = "root";
$password = ""; // Default password for XAMPP is empty
$dbname = "fbla";

// Establish a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form data is set
if (isset($_POST['email'], $_POST['password'])) {

    // Get form data
    $email = $_POST['email'];
    $password = $_POST['password'];

    // SQL query to check the user in both 'students' and 'employers' tables
    // Make sure to select the same number of columns in both SELECT statements
    $sql = "
    SELECT id, fname, lname, email, pass, 'student' AS user_type FROM students WHERE email = ? 
    UNION 
    SELECT id, fname, lname, email, pass, 'employer' AS user_type FROM employers WHERE email = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the email exists in either table
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['pass'])) {
            // Store user info in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['user_type'] = $user['user_type'];  // 'student' or 'employer'

            // Redirect to respective dashboard based on user type
            if ($user['user_type'] == 'student') {
                header("Location: studentdash.php");
                exit();
            } else {
                header("Location: employerdash.html");
                exit();
            }
        } else {
            echo "Incorrect password!";
        }
    } else {
        echo "No user found with this email.";
    }

    // Close the statement
    $stmt->close();
} else {
    echo "Please enter email and password.";
}

// Close the connection
$conn->close();
?>
