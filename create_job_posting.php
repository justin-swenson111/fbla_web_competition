<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "mysql";
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

// Get employer's company information automatically
$employer_id = $_SESSION['user_id'];
$company_query = "SELECT company_name FROM employers WHERE id = ?";
$stmt = $conn->prepare($company_query);
$stmt->bind_param("i", $employer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Employer information not found.");
}

$employer_info = $result->fetch_assoc();
$authorized_company = $employer_info['company_name'];
$stmt->close();

// Handle job posting
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if all required fields are present
    $required_fields = ['title', 'description', 'requirements', 'salary', 'location', 'job_type', 'deadline'];
    $missing_fields = [];
    
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            $missing_fields[] = $field;
        }
    }
    
    if (!empty($missing_fields)) {
        echo "Missing required fields: " . implode(", ", $missing_fields);
        exit;
    }

    $title = $_POST['title'];
    $description = $_POST['description'];
    $requirements = $_POST['requirements'];
    $salary = $_POST['salary'];
    $location = $_POST['location'];
    $job_type = $_POST['job_type'];
    $application_deadline = $_POST['deadline'];  // Match the form field name

    // Insert the job posting into the database
    $sql = "INSERT INTO job_postings (
                employer_id, 
                company_name, 
                title, 
                description, 
                requirements, 
                salary, 
                location, 
                job_type, 
                application_deadline
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "issssdsss", 
        $employer_id, 
        $authorized_company, 
        $title, 
        $description, 
        $requirements, 
        $salary, 
        $location,
        $job_type,
        $application_deadline
    );

    if ($stmt->execute()) {
        echo "Job posted successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Invalid request method.";
}

$conn->close();
?>