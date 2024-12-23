<?php
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

// Check if all required form data is set
if (isset($_POST['fname'], $_POST['lname'], $_POST['email'], $_POST['password'], $_POST['user_type'])) {

    // Get form data
    $first_name = $_POST['fname'];
    $last_name = $_POST['lname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $user_type = $_POST['user_type']; // 'student' or 'employer'

    // Check if passwords match
    if ($_POST['password'] !== $_POST['repeat-password']) {
        die("Passwords do not match.");
    }

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Handle based on user type
    if ($user_type === "student") {
        // Insert into Students table
        $sql = "INSERT INTO students (fname, lname, email, pass, user_type) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $first_name, $last_name, $email, $hashed_password, $user_type);
    } elseif ($user_type === "employer") {
        // Check if company_name is set for employers
        if (isset($_POST['company_name'])) {
            $company_name = $_POST['company_name'];

            // Insert into Employers table
            $sql = "INSERT INTO employers (fname, lname, email, pass, company_name, user_type) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $first_name, $last_name, $email, $hashed_password, $company_name, $user_type);
        } else {
            die("Company name is required for employer registration.");
        }
    } else {
        die("Invalid user type specified.");
    }

    // Execute the query
    if ($stmt->execute()) {
        echo "Registration successful!";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement and connection
    $stmt->close();
} else {
    die("Required form data missing.");
}

$conn->close();
?>
