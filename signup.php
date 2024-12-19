<?php
// Database connection settings
$servername = "localhost";
$username = "root";     // Your MySQL username
$password = "";         // Your MySQL password
$dbname = "db1";        // Your database name

// Create a connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process the form data
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $firstName = $_POST['first-name'];
    $lastName = $_POST['last-name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Simple validation (add more as needed)
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
        die("All fields are required.");
    }

    // Hash the password for security
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Prepare SQL statement to insert data
    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $firstName, $lastName, $email, $hashedPassword);

    // Execute the statement
    if ($stmt->execute()) {
        echo "Signup successful! You can now <a href='login.html'>login</a>.";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement and connection
    $stmt->close();
}

$conn->close();
?>
