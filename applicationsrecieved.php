<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "mysql"; 
$dbname = "fbla";

// Database connection
$conn = new mysqli($servername, $username, $password, $dbname);

$isLoggedIn = isset($_SESSION['user_id']);
$userType = $_SESSION['user_type'] ?? null;
$userId = $_SESSION['user_id'] ?? null;

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
    
    // Just redirect back to the same page without any parameters
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Handle application deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['application_id'])) {
    $application_id = $conn->real_escape_string($_POST['application_id']);
    
    $delete_query = "DELETE FROM job_applications 
                     WHERE id = '$application_id' 
                     AND employer_id = '$employer_id'";
    
    $conn->query($delete_query);
    exit();
}

// Handle job posting deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'deleteJob' && isset($_POST['job_id'])) {
    $job_id = $conn->real_escape_string($_POST['job_id']);
    
    // First delete all applications for this job
    $delete_applications_query = "DELETE FROM job_applications 
                                 WHERE job_posting_id = '$job_id' 
                                 AND employer_id = '$employer_id'";
    $conn->query($delete_applications_query);
    
    // Then delete the job posting itself
    $delete_job_query = "DELETE FROM job_postings 
                         WHERE id = '$job_id' 
                         AND employer_id = '$employer_id'";
    
    $conn->query($delete_job_query);
    exit();
}

// First, get all job postings by this employer
$job_query = "SELECT 
               jp.id as job_posting_id,
               jp.title as job_title,
               jp.company_name
             FROM job_postings jp
             WHERE jp.employer_id = '$employer_id'
             ORDER BY jp.title";

$job_result = $conn->query($job_query);

// Create an array to store all job postings
$grouped_applications = [];
while ($job_row = $job_result->fetch_assoc()) {
    $job_id = $job_row['job_posting_id'];
    $grouped_applications[$job_id] = [
        'job_title' => $job_row['job_title'],
        'company_name' => $job_row['company_name'],
        'applications' => []
    ];
}

// Now get all applications and add them to the appropriate job
$application_query = "SELECT 
                        ja.id as application_id,
                        ja.status,
                        ja.applied_at,
                        ja.cover_letter,
                        ja.resume_path,
                        ja.job_posting_id,
                        s.fname,
                        s.lname,
                        s.email,
                        s.bio,
                        s.skills,
                        s.profile_picture
                     FROM job_applications ja
                     JOIN students s ON ja.student_id = s.id
                     WHERE ja.employer_id = '$employer_id'
                     ORDER BY ja.applied_at DESC";

$app_result = $conn->query($application_query);

while ($app_row = $app_result->fetch_assoc()) {
    $job_id = $app_row['job_posting_id'];
    if (isset($grouped_applications[$job_id])) {
        $grouped_applications[$job_id]['applications'][] = $app_row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Applications</title>
    <link rel="shortcut icon" href="./media/favicon.ico" type="image/x-icon" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* Navbar */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.8rem 4rem;
            background-color: rgba(44, 62, 80, 0.9);
            position: sticky;
            top: 0;
            width: 100%;
            z-index: 1000;
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar .logo {
            height: 50px;
            width: auto;
            transition: all 0.3s ease;
        }

        /* Navigation Links */
        .nav-links {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-size: 1rem;
            font-weight: 600;
            padding: 0.7rem 1.2rem;
            border-radius: 4px;
            position: relative;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        /* Hover and Active States */
        .nav-links a:hover,
        .nav-links a.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: #f57f17;
            transform: translateY(-2px);
        }

        .nav-links a::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: 0;
            width: 0;
            height: 3px;
            background-color: #f57f17;
            transition: width 0.3s ease;
        }

        .nav-links a:hover::after,
        .nav-links a.active::after {
            width: 100%;
        }

        /* Profile Link and Picture */
        .profile-link {
            padding: 0.5rem !important;
            margin-left: 0.5rem;
            display: flex;
            align-items: center;
        }

        .profile-pic {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
            transition: transform 0.3s ease;
            border: 2px solid transparent;
        }

        .profile-pic:hover {
            transform: scale(1.1);
            border-color: #f57f17;
        }

        /* Responsive Adjustments */
        @media screen and (max-width: 1024px) {
            .navbar {
                padding: 0.8rem 2rem;
            }
            
            .nav-links {
                gap: 1rem;
            }
            
            .nav-links a {
                padding: 0.6rem 1rem;
            }
        }

        @media screen and (max-width: 768px) {
            .navbar {
                padding: 1rem;
                flex-direction: column;
                gap: 1rem;
            }

            .navbar .logo {
                height: 40px;
            }

            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
                width: 100%;
                gap: 0.5rem;
            }

            .nav-links a {
                font-size: 0.9rem;
                padding: 0.5rem 0.8rem;
                text-align: center;
            }

            .profile-link {
                margin: 0;
            }
        }

        @media screen and (max-width: 480px) {
            .navbar {
                padding: 0.8rem 0.5rem;
            }

            .navbar .logo {
                height: 35px;
            }

            .nav-links {
                gap: 0.3rem;
            }

            .nav-links a {
                font-size: 0.85rem;
                padding: 0.4rem 0.6rem;
            }
        }
        
                /* Job Section Styling */
        .job-section {
            margin-bottom: 2rem;
            border: 1px solid #e0e0e0;
            border-radius: 0.5rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            overflow: hidden;
            transition: box-shadow 0.3s ease;
        }

        .job-section:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        /* Job Header Styling */
        .job-header {
            background-color: #f8f9fa;
            padding: 1.25rem;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e0e0e0;
            transition: background-color 0.2s ease;
        }

        .job-header:hover {
            background-color: #e9ecef;
        }

        .job-header h4 {
            margin: 0;
            color: #343a40;
            font-weight: 600;
        }

        /* Application Count Badge */
        .application-count {
            background-color: #6c757d;
            color: white;
            padding: 0.35rem 0.75rem;
            border-radius: 2rem;
            font-size: 0.875rem;
            font-weight: 500;
            display: inline-block;
            letter-spacing: 0.3px;
        }

        /* Applications Container */
        /* Enhanced Applications Container Styling */
        .applications-container {
            display: none;
            background-color: #ffffff;
            border-radius: 0 0 0.5rem 0.5rem;
            transition: all 0.3s ease;
        }

        .applications-container.show {
            display: block;
            animation: slideDown 0.4s ease;
            box-shadow: inset 0 5px 15px rgba(0,0,0,0.03);
        }

        @keyframes slideDown {
            from { 
                opacity: 0;
                transform: translateY(-10px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Enhanced Table Styling */
        .table {
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            padding: 1rem;
            color: #495057;
            border-bottom: 2px solid #e9ecef;
        }

        .table td {
            padding: 1.25rem 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f3f5;
            transition: background-color 0.2s ease;
        }

        .table tr:hover td {
            background-color: #f8f9fa;
        }

        .table tr:last-child td {
            border-bottom: none;
        }

        /* Improved Applicant Profile */
        td .applicant-info {
            display: flex;
            align-items: center;
        }

        td img.rounded-circle {
            width: 55px;
            height: 55px;
            object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 0 3px 8px rgba(0,0,0,0.12);
            margin-right: 12px;
            transition: transform 0.3s ease;
        }

        td img.rounded-circle:hover {
            transform: scale(1.05);
        }

        /* Enhanced Status Badges */
        .badge {
            padding: 0.5rem 0.75rem;
            font-weight: 500;
            border-radius: 4px;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            display: inline-block;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        }

        /* Improved Buttons Styling */
        .btn-sm {
            border-radius: 4px;
            font-weight: 500;
            padding: 0.35rem 0.7rem;
            transition: all 0.2s ease;
            border: none;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            letter-spacing: 0.3px;
        }

        .btn-sm:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .btn-sm:active {
            transform: translateY(0);
            box-shadow: 0 2px 3px rgba(0,0,0,0.1);
        }

        /* Form Elements */
        .form-select-sm {
            border-radius: 4px;
            padding: 0.4rem 0.7rem;
            font-size: 0.875rem;
            border: 1px solid #ced4da;
            background-color: #f8f9fa;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-select-sm:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }

        /* Enhanced Empty State */
        .applications-container .empty-state {
            padding: 3rem 2rem;
            text-align: center;
        }

        .empty-state .text-muted {
            font-style: italic;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 3rem;
            color: #dee2e6;
            margin-bottom: 1rem;
            display: block;
        }

        /* Skills and Bio Display */
        .skills-bio {
            max-width: 300px;
        }

        .skills-bio small strong {
            color: #495057;
        }

        .skills-tag {
            display: inline-block;
            background-color: #e9ecef;
            color: #495057;
            border-radius: 30px;
            padding: 0.2rem 0.6rem;
            margin: 0.1rem;
            font-size: 0.7rem;
            font-weight: 500;
        }

        /* Cover Letter Modal Styling */
        .modal-content {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .modal-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            padding: 1.25rem 1.5rem;
        }

        .modal-body {
            padding: 1.5rem;
            line-height: 1.6;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .table td, .table th {
                padding: 0.8rem 0.6rem;
            }
            
            .applicant-info {
                flex-direction: column;
                align-items: flex-start;
            }
            
            td img.rounded-circle {
                margin-bottom: 0.5rem;
                margin-right: 0;
            }
            
            .btn-group-sm {
                display: flex;
                flex-direction: column;
            }
            
            .btn-group-sm .btn {
                margin-bottom: 0.3rem;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">    
      <a href="./index.php">
          <img src="./media/logo.png" alt="Logo" class="logo" height="200px" width="auto" />
      </a>
      <div class="nav-links">
          <a href="./aboutUs.php">About Us</a>
          <a href="./resources.php">Resources</a>
          <?php if ($isLoggedIn): ?>
              <?php if ($userType === 'student'): ?>
                    <a href="scholarships.php">Scholarships</a>
                  <a href="studentdash.php">Student Dashboard</a>
              <?php endif; ?>
              <?php if ($userType === 'employer'): ?>
                  <a href="./employerdash.php">Job Postings</a>
                  <a href="./applicationsrecieved.php">View Applications</a>
              <?php endif; ?>
              <?php if ($userType === 'admin'): ?>
                  <a href="./admindash.php">Admin Dashboard</a>
              <?php endif; ?>
              <a href="./settings.php" class="profile-link">
                  <img src="<?php 
                      $table = ($userType === 'student') ? 'students' : 
                              (($userType === 'employer') ? 'employers' : 'admins');
                      $stmt = $conn->prepare("SELECT profile_picture FROM $table WHERE id = ?");
                      $stmt->bind_param("i", $_SESSION['user_id']);
                      $stmt->execute();
                      $result = $stmt->get_result();
                      $profile = $result->fetch_assoc();
                      $stmt->close();
                      echo !empty($profile['profile_picture']) ? htmlspecialchars($profile['profile_picture']) : './media/default-image.png';
                  ?>" 
                  alt="Profile" 
                  class="profile-pic" />
              </a>
          <?php else: ?>
              <a href="./loginpage.php">Login</a>
          <?php endif; ?>
      </div>
    </div> 

    <div class="container mt-5">
        <h2>Manage Job Applications</h2>
        
        <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success" role="alert">
            Application status updated successfully!
        </div>
        <?php endif; ?>

        <?php foreach ($grouped_applications as $job_id => $job_data): ?>
        <div class="job-section" id="job-section-<?php echo $job_id; ?>">
            <div class="job-header" onclick="toggleApplications('job-<?php echo $job_id; ?>')">
                <div>
                    <h4 class="mb-0"><?php echo htmlspecialchars($job_data['job_title']); ?></h4>
                    <small class="text-muted"><?php echo htmlspecialchars($job_data['company_name']); ?></small>
                </div>
                <div class="d-flex align-items-center">
                    <span class="application-count me-3">
                        <?php echo count($job_data['applications']); ?> Applications
                    </span>
                    <button 
                        onclick="event.stopPropagation(); deleteJob(<?php echo $job_id; ?>)" 
                        class="btn btn-sm btn-danger">
                        Delete Job
                    </button>
                </div>
            </div>
            
            <div class="applications-container" id="job-<?php echo $job_id; ?>">
                <?php if (count($job_data['applications']) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Applicant</th>
                                <th>Skills & Experience</th>
                                <th>Applied Date</th>
                                <th>Status</th>
                                <th>Documents</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($job_data['applications'] as $application): ?>
                            <tr data-application-id="<?php echo $application['application_id']; ?>">
                                <td>
                                    <?php if(isset($application['profile_picture']) && $application['profile_picture']): ?>
                                        <img src="<?php echo htmlspecialchars($application['profile_picture']); ?>" 
                                             class="rounded-circle mb-2" 
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                    <?php endif; ?>
                                    <div>
                                        <?php echo htmlspecialchars($application['fname'] . ' ' . $application['lname']); ?><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($application['email']); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <?php if(isset($application['skills']) && $application['skills']): ?>
                                        <small class="d-block"><strong>Skills:</strong> <?php echo htmlspecialchars($application['skills']); ?></small>
                                    <?php endif; ?>
                                    <?php if(isset($application['bio']) && $application['bio']): ?>
                                        <small class="d-block mt-1"><strong>Bio:</strong> <?php echo htmlspecialchars(substr($application['bio'], 0, 100)) . '...'; ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($application['applied_at'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo match($application['status']) {
                                            'Accepted' => 'success',
                                            'Rejected' => 'danger',
                                            'Reviewed' => 'info',
                                            default => 'warning'
                                        };
                                    ?>">
                                        <?php echo htmlspecialchars($application['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (isset($application['resume_path']) && $application['resume_path']): ?>
                                        <a href="<?php echo htmlspecialchars($application['resume_path']); ?>" class="btn btn-sm btn-secondary" target="_blank">View Resume</a>
                                    <?php endif; ?>
                                    <?php if (isset($application['cover_letter']) && $application['cover_letter']): ?>
                                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#coverLetter<?php echo $application['application_id']; ?>">
                                            View Cover Letter
                                        </button>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form onsubmit="event.preventDefault(); updateApplicationStatus(this);" class="d-inline">
                                        <input type="hidden" name="application_id" value="<?php echo $application['application_id']; ?>">
                                        <select name="status" class="form-select form-select-sm mb-2" required>
                                            <option value="">Update Status</option>
                                            <option value="Pending">Pending</option>
                                            <option value="Reviewed">Reviewed</option>
                                            <option value="Accepted">Accepted</option>
                                            <option value="Rejected">Rejected</option>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                    </form>
                                    <button 
                                        onclick="deleteApplication(<?php echo $application['application_id']; ?>)" 
                                        class="btn btn-sm btn-danger ms-2">
                                        Delete
                                    </button>
                                </td>
                            </tr>

                            <!-- Cover Letter Modal -->
                            <?php if (isset($application['cover_letter']) && $application['cover_letter']): ?>
                            <div class="modal fade" id="coverLetter<?php echo $application['application_id']; ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Cover Letter</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <?php echo nl2br(htmlspecialchars($application['cover_letter'])); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="p-4 text-center">
                    <p class="text-muted">No applications yet for this job posting.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Global variable for alert z-index
        var alertZIndex = 1050;
        
        // Toggle applications visibility
        function toggleApplications(jobId) {
            const container = document.getElementById(jobId);
            container.classList.toggle('show');
        }

        function deleteJob(jobId) {
            if (confirm('Are you sure you want to delete this job posting? This will also delete all applications. This action cannot be undone.')) {
                fetch('', {
                    method: 'POST',
                    body: new URLSearchParams({
                        action: 'deleteJob',
                        job_id: jobId
                    }),
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    }
                })
                .then(response => response.text())
                .then(data => {
                    // Remove the entire job section
                    const jobSection = document.getElementById('job-section-' + jobId);
                    if (jobSection) {
                        jobSection.remove();
                    }
                    
                    showAlert('Job posting deleted successfully!', 'success');
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('An error occurred while deleting the job posting.', 'danger');
                });
            }
        }

        function deleteApplication(applicationId) {
            if (confirm('Are you sure you want to delete this application? This action cannot be undone.')) {
                fetch('', {
                    method: 'POST',
                    body: new URLSearchParams({
                        action: 'delete',
                        application_id: applicationId
                    }),
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    }
                })
                .then(response => response.text())
                .then(data => {
                    // Find the row and its parent job section
                    const row = document.querySelector(`tr[data-application-id="${applicationId}"]`);
                    if (row) {
                        const jobSection = row.closest('.job-section');
                        const applicationsContainer = row.closest('.applications-container');
                        const tbody = row.closest('tbody');
                        
                        // Remove the row
                        row.remove();
                        
                        // Update the count in the header
                        if (jobSection) {
                            const countElement = jobSection.querySelector('.application-count');
                            const remainingRows = tbody ? tbody.querySelectorAll('tr').length : 0;
                            
                            // Update the count
                            countElement.textContent = `${remainingRows} Application${remainingRows === 1 ? '' : 's'}`;
                            
                            // If no applications left, show the "No applications yet" message
                            if (remainingRows === 0) {
                                const table = applicationsContainer.querySelector('.table-responsive');
                                if (table) {
                                    table.remove();
                                    const noAppsDiv = document.createElement('div');
                                    noAppsDiv.className = 'p-4 text-center';
                                    noAppsDiv.innerHTML = '<p class="text-muted">No applications yet for this job posting.</p>';
                                    applicationsContainer.appendChild(noAppsDiv);
                                }
                            }
                        }
                    }
                    showAlert('Application deleted successfully!', 'success');
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('An error occurred while deleting the application.', 'danger');
                });
            }
        }
        
        function updateApplicationStatus(form) {
            const formData = new FormData(form);
            const applicationId = formData.get('application_id');
            const status = formData.get('status');
            
            // Set the variables for job, company, and applicant name
            const nameElement = form.closest('tr').querySelector('td:first-child div');
            const jobElement = form.closest('.job-section').querySelector('.job-header h4');
            const companyElement = form.closest('.job-section').querySelector('.job-header small');

            if (nameElement && jobElement && companyElement) {
                const name = nameElement.childNodes[0].textContent.trim(); // Get the name (first child node)
                const email = nameElement.querySelector('small').textContent.trim(); // Get the email (inside <small>)
                const job = jobElement.innerText;
                const company = companyElement.innerHTML;
                
                // Call the email.php script
                fetch('email.php', {
                    method: 'POST',
                    body: new URLSearchParams({
                        applicant: name,
                        status: status,
                        job: job,
                        company: company,
                        recipient: email
                    }),
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    }
                })
                .then(response => response.text())
                .then(data => {
                    console.log('Email script executed successfully:', data);
                })
                .catch(error => {
                    console.error('Error executing email script:', error);
                });
            }
            
            if (!status) {
                alert('Please select a status.');
                return;
            }

            fetch('', {
                method: 'POST',
                body: new URLSearchParams({
                    application_id: applicationId,
                    status: status
                }),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                }
            })
            .then(response => response.text())
            .then(data => {
                // Find the status badge element
                const row = form.closest('tr');
                const statusBadge = row.querySelector('.badge');
                
                // Update the badge visuals
                statusBadge.classList.remove('bg-warning', 'bg-info', 'bg-success', 'bg-danger');
                
                // Set the appropriate background class based on status
                switch(status) {
                    case 'Accepted':
                        statusBadge.classList.add('bg-success');
                        break;
                    case 'Rejected':
                        statusBadge.classList.add('bg-danger');
                        break;
                    case 'Reviewed':
                        statusBadge.classList.add('bg-info');
                        break;
                    default:
                        statusBadge.classList.add('bg-warning');
                }
                
                // Update the badge text
                statusBadge.textContent = status;
                
                // Reset the select element
                form.querySelector('select').value = '';
                
                // Show success message
                showAlert('Status updated successfully!', 'success');
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred while updating the status.', 'danger');
            });
        }

        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
            alertDiv.style.zIndex = alertZIndex++;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            document.body.appendChild(alertDiv);
            
            // Remove the alert after 3 seconds
            setTimeout(() => {
                alertDiv.remove();
            }, 3000);
        }

        // Initialize event listeners when the document is ready
        document.addEventListener('DOMContentLoaded', function() {
            // Add submit event listeners to all forms
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function(event) {
                    event.preventDefault();
                });
            });

            // Initialize any Bootstrap components if needed
            if (typeof bootstrap !== 'undefined') {
                // Initialize tooltips
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });

                // Initialize popovers
                var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
                popoverTriggerList.map(function (popoverTriggerEl) {
                    return new bootstrap.Popover(popoverTriggerEl);
                });
            }
        });
    </script>
</body>
</html>