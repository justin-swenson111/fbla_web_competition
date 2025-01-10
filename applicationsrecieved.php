<?php
session_start();

$servername = "localhost";
$username = "root";
$password = ""; // Default password for XAMPP is empty
$dbname = "fbla";

// Database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the user is logged in and is an employer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'employer') {
    header('Location: login.php');
    exit();
}

$employer_id = $_SESSION['user_id'];

// Handle application status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['application_id']) && isset($_POST['status'])) {
    $application_id = $conn->real_escape_string($_POST['application_id']);
    $status = $conn->real_escape_string($_POST['status']);
    
    $update_query = "UPDATE job_applications 
                    SET status = '$status', 
                        updated_at = CURRENT_TIMESTAMP 
                    WHERE id = '$application_id' 
                    AND employer_id = '$employer_id'";
    
    $conn->query($update_query);
    
    // Redirect to prevent form resubmission
    header('Location: applications.php?success=1');
    exit();
}

// Fetch all applications for this employer's job postings
$query = "SELECT 
            ja.id as application_id,
            ja.status,
            ja.applied_at,
            ja.cover_letter,
            ja.resume_path,
            jp.title as job_title,
            jp.company_name,
            s.fname,
            s.lname,
            s.email,
            s.bio,
            s.skills,
            s.profile_picture
          FROM job_applications ja
          JOIN job_postings jp ON ja.job_posting_id = jp.id
          JOIN students s ON ja.student_id = s.id
          WHERE ja.employer_id = '$employer_id'
          ORDER BY ja.applied_at DESC";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Applications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Manage Job Applications</h2>
        
        <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success" role="alert">
            Application status updated successfully!
        </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Job Title</th>
                        <th>Applicant</th>
                        <th>Skills & Experience</th>
                        <th>Applied Date</th>
                        <th>Status</th>
                        <th>Documents</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <?php echo htmlspecialchars($row['job_title']); ?><br>
                            <small class="text-muted"><?php echo htmlspecialchars($row['company_name']); ?></small>
                        </td>
                        <td>
                            <?php if($row['profile_picture']): ?>
                                <img src="<?php echo htmlspecialchars($row['profile_picture']); ?>" 
                                     class="rounded-circle mb-2" 
                                     style="width: 50px; height: 50px; object-fit: cover;">
                            <?php endif; ?>
                            <div>
                                <?php echo htmlspecialchars($row['fname'] . ' ' . $row['lname']); ?><br>
                                <small class="text-muted"><?php echo htmlspecialchars($row['email']); ?></small>
                            </div>
                        </td>
                        <td>
                            <?php if($row['skills']): ?>
                                <small class="d-block"><strong>Skills:</strong> <?php echo htmlspecialchars($row['skills']); ?></small>
                            <?php endif; ?>
                            <?php if($row['bio']): ?>
                                <small class="d-block mt-1"><strong>Bio:</strong> <?php echo htmlspecialchars(substr($row['bio'], 0, 100)) . '...'; ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($row['applied_at'])); ?></td>
                        <td>
                            <span class="badge bg-<?php 
                                echo match($row['status']) {
                                    'Accepted' => 'success',
                                    'Rejected' => 'danger',
                                    'Reviewed' => 'info',
                                    default => 'warning'
                                };
                            ?>">
                                <?php echo htmlspecialchars($row['status']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($row['resume_path']): ?>
                                <a href="<?php echo htmlspecialchars($row['resume_path']); ?>" class="btn btn-sm btn-secondary" target="_blank">View Resume</a>
                            <?php endif; ?>
                            <?php if ($row['cover_letter']): ?>
                                <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#coverLetter<?php echo $row['application_id']; ?>">
                                    View Cover Letter
                                </button>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="application_id" value="<?php echo $row['application_id']; ?>">
                                <select name="status" class="form-select form-select-sm mb-2" required>
                                    <option value="">Update Status</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Reviewed">Reviewed</option>
                                    <option value="Accepted">Accepted</option>
                                    <option value="Rejected">Rejected</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-primary">Update</button>
                            </form>
                        </td>
                    </tr>

                    <!-- Cover Letter Modal -->
                    <?php if ($row['cover_letter']): ?>
                    <div class="modal fade" id="coverLetter<?php echo $row['application_id']; ?>" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Cover Letter</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <?php echo nl2br(htmlspecialchars($row['cover_letter'])); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>