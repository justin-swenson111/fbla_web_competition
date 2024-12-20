<?php
// Database connection details
$servername = "localhost";
$username = "root";
$password = ""; // Default password for XAMPP is empty
$dbname = "db1";

// Establish a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$first_name = $_POST['fname'];
$last_name = $_POST['lname'];
$email = $_POST['email'];
$password = $_POST['password'];

// Hash the password for security
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// SQL query to insert data
$sql = "INSERT INTO user (fname, lname, email, pass) VALUES (?, ?, ?, ?)";

// Prepare and bind
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $first_name, $last_name, $email, $hashed_password);

// Execute the query
if ($stmt->execute()) {
    echo "Registration successful!";
} else {
    echo "Error: " . $stmt->error;
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>
