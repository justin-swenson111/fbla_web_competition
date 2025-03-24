<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username = "root";
$password = "mysql";
$dbname = "fbla";

// Connect to database
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Check if student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    die("Unauthorized access. Please log in as a student.");
}

echo "Step 2: Valid session.<br>";

// Handle job application
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['job_posting_id']) || empty(trim($_POST['cover_letter']))) {
        die("All fields are required.");
    }

    $job_posting_id = intval($_POST['job_posting_id']);
    $cover_letter = htmlspecialchars(trim($_POST['cover_letter']), ENT_QUOTES, 'UTF-8');
    $student_id = $_SESSION['user_id'];

    echo "Step 3: Input validated.<br>";

    // Get employer_id from job_posting
    $employer_query = "SELECT employer_id FROM job_postings WHERE id = ?";
    $employer_stmt = $conn->prepare($employer_query);
    $employer_stmt->bind_param("i", $job_posting_id);
    $employer_stmt->execute();
    $employer_result = $employer_stmt->get_result();

    if ($employer_result->num_rows === 0) {
        die("Invalid job posting.");
    }

    $employer_id = $employer_result->fetch_assoc()['employer_id'];
    $employer_stmt->close();

    echo "Step 4: Employer found.<br>";

    // Check if already applied
    $check_sql = "SELECT id FROM job_applications WHERE job_posting_id = ? AND student_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $job_posting_id, $student_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        die("You have already applied to this job.");
    }
    $check_stmt->close();

    // Handle resume upload
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/resumes/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_extension = strtolower(pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION));
        $file_name = uniqid('resume_') . '.' . $file_extension;
        $target_path = $upload_dir . $file_name;

        $allowed_types = ['pdf', 'doc', 'docx'];
        if (!in_array($file_extension, $allowed_types)) {
            die("Only PDF, DOC, or DOCX files are allowed.");
        }

        if (!move_uploaded_file($_FILES['resume']['tmp_name'], $target_path)) {
            die("Error uploading resume.");
        }
        $resume_path = $target_path;

        echo "Step 5: Resume uploaded successfully.<br>";
    } else {
        die("Please upload a valid resume.");
    }

    // Insert application
    $sql = "INSERT INTO job_applications (job_posting_id, student_id, employer_id, cover_letter, resume_path, status, applied_at) 
            VALUES (?, ?, ?, ?, ?, 'Pending', NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiss", $job_posting_id, $student_id, $employer_id, $cover_letter, $resume_path);

    if ($stmt->execute()) {
        echo "Step 6: Application submitted.<br>";
        header("Location: studentdash.php");
        exit();
    } else {
        die("Error submitting application: " . $stmt->error);
    }

    $stmt->close();
} else {
    die("Invalid request method.");
}

$conn->close();
?>
