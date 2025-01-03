<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fbla";

// Connect to database
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if employer is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'employer') {
    die("Unauthorized access.");
}

// Handle job posting
if (isset($_POST['title'], $_POST['description'], $_POST['requirements'], $_POST['salary'], $_POST['location'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $requirements = $_POST['requirements'];
    $salary = $_POST['salary'];
    $location = $_POST['location'];
    $employer_id = $_SESSION['user_id']; // Employer's ID

    $sql = "INSERT INTO job_postings (employer_id, title, description, requirements, salary, location) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssds", $employer_id, $title, $description, $requirements, $salary, $location);

    if ($stmt->execute()) {
        echo "Job posted successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "All fields are required.";
}

$conn->close();
?>
