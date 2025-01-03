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

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if all required form data is set
    if (isset($_POST['fname'], $_POST['lname'], $_POST['email'], $_POST['password'], $_POST['user_type'])) {
        // Get form data
        $first_name = $conn->real_escape_string($_POST['fname']);
        $last_name = $conn->real_escape_string($_POST['lname']);
        $email = $conn->real_escape_string($_POST['email']);
        $password = $_POST['password'];
        $user_type = $conn->real_escape_string($_POST['user_type']);

        // Check if passwords match
        if (!isset($_POST['repeat-password']) || $_POST['password'] !== $_POST['repeat-password']) {
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
                $company_name = $conn->real_escape_string($_POST['company_name']);

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
            // Redirect to success page or login page
            header("Location: login.html");
            exit();
        } else {
            die("Error: " . $stmt->error);
        }

        // Close the statement
        $stmt->close();
    } else {
        die("Required form data missing.");
    }
} else {
    die("Invalid request method.");
}

$conn->close();
?>