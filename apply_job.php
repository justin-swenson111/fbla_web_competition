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

// Check if student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    die("Unauthorized access.");
}

// Handle job application
if (isset($_POST['job_id'], $_POST['application_message'])) {
    $job_id = $_POST['job_id'];
    $application_message = $_POST['application_message'];
    $student_id = $_SESSION['user_id']; // Student's ID

    $sql = "INSERT INTO job_applications (job_id, student_id, application_message) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $job_id, $student_id, $application_message);

    if ($stmt->execute()) {
        echo "Application submitted successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "All fields are required.";
}

$conn->close();
?>
