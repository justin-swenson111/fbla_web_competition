<?php
session_start();

$servername = "localhost";
$username = "root";
$password = ""; 
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

// Modified query to group applications by job posting
$query = "SELECT 
            ja.id as application_id,
            ja.status,
            ja.applied_at,
            ja.cover_letter,
            ja.resume_path,
            jp.id as job_posting_id,
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
          ORDER BY jp.title, ja.applied_at DESC";

$result = $conn->query($query);

// Group applications by job title
$grouped_applications = [];
while ($row = $result->fetch_assoc()) {
    $job_id = $row['job_posting_id'];
    if (!isset($grouped_applications[$job_id])) {
        $grouped_applications[$job_id] = [
            'job_title' => $row['job_title'],
            'company_name' => $row['company_name'],
            'applications' => []
        ];
    }
    $grouped_applications[$job_id]['applications'][] = $row;
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
        
        .job-section {
            margin-bottom: 2rem;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
        }

        .job-header {
            background-color: #f8f9fa;
            padding: 1rem;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .job-header:hover {
            background-color: #e9ecef;
        }

        .application-count {
            background-color: #6c757d;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.875rem;
        }

        .applications-container {
            display: none;
        }

        .applications-container.show {
            display: block;
        }
    </style>
</head>
<body>
    <div class="navbar">    
        <a href="index.php">
            <img src="./media/logo.png" alt="Logo" class="logo" height="200px" width="auto" />
        </a>
        <div class="nav-links">
            <a href="aboutUs.php">About Us</a>
            <a href="resources.php">Resources</a>
            <?php if ($isLoggedIn): ?>
                <?php if ($userType === 'student'): ?>
                    <a href="studentdash.php">Student Dashboard</a>
                <?php endif; ?>
                <?php if ($userType === 'employer'): ?>
                    <a href="employerdash.php">Job Postings</a>
                    <a href="applicationsrecieved.php">View Applications</a>
                <?php endif; ?>
                <a href="settings.php" class="profile-link">
                    <img src="<?php 
                        $table = ($userType === 'student') ? 'students' : 'employers';
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
                <a href="loginpage.php">Login</a>
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
        <div class="job-section">
            <div class="job-header" onclick="toggleApplications('job-<?php echo $job_id; ?>')">
                <div>
                    <h4 class="mb-0"><?php echo htmlspecialchars($job_data['job_title']); ?></h4>
                    <small class="text-muted"><?php echo htmlspecialchars($job_data['company_name']); ?></small>
                </div>
                <span class="application-count">
                    <?php echo count($job_data['applications']); ?> Applications
                </span>
            </div>
            
            <div class="applications-container" id="job-<?php echo $job_id; ?>">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr data-application-id="<?php echo $application['application_id']; ?>">
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
                                    <?php if($application['profile_picture']): ?>
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
                                    <?php if($application['skills']): ?>
                                        <small class="d-block"><strong>Skills:</strong> <?php echo htmlspecialchars($application['skills']); ?></small>
                                    <?php endif; ?>
                                    <?php if($application['bio']): ?>
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
                                    <?php if ($application['resume_path']): ?>
                                        <a href="<?php echo htmlspecialchars($application['resume_path']); ?>" class="btn btn-sm btn-secondary" target="_blank">View Resume</a>
                                    <?php endif; ?>
                                    <?php if ($application['cover_letter']): ?>
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
                                    <!-- Add this button -->
                                    <button 
                                        onclick="deleteApplication(<?php echo $application['application_id']; ?>)" 
                                        class="btn btn-sm btn-danger ms-2">
                                        Delete
                                    </button>
                                </td>
                            </tr>

                            <!-- Cover Letter Modal -->
                            <?php if ($application['cover_letter']): ?>
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
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle applications visibility
        function toggleApplications(jobId) {
            const container = document.getElementById(jobId);
            container.classList.toggle('show');
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
                            const remainingRows = tbody.querySelectorAll('tr').length;
                            
                            if (remainingRows === 0) {
                                // If this was the last application, remove the entire job section
                                jobSection.remove();
                            } else {
                                // Otherwise, update the count
                                countElement.textContent = `${remainingRows} Application${remainingRows === 1 ? '' : 's'}`;
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
            
            // Find the status badge element
            const row = form.closest('tr');
            const statusBadge = row.querySelector('.badge');
            
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
                    updateApplicationStatus(this);
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