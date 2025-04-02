<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "mysql";
$dbname = "fbla";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$isLoggedIn = isset($_SESSION['user_id']);
$userType = $_SESSION['user_type'] ?? null;
$userId = $_SESSION['user_id'] ?? null;

// Check if user is logged in and is an admin
if (!$isLoggedIn || $userType !== 'admin') {
    header("Location: loginpage.php");
    exit();
}

// Handle AJAX-based approval/rejection requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        if (isset($_POST['action']) && isset($_POST['id'])) {
            $id = (int)$_POST['id'];
            $action = $_POST['action'];
            
            if ($action === 'approve_job_posting') {
                $stmt = $conn->prepare("UPDATE job_posting_waitlist SET approval_status = 'Approved' WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->close();
                echo json_encode(["success" => true, "message" => "Job posting approved successfully!"]);
            } elseif ($action === 'reject_job_posting' && isset($_POST['reason'])) {
                $reason = htmlspecialchars($_POST['reason']);
                $stmt = $conn->prepare("DELETE FROM job_posting_waitlist WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->close();
                echo json_encode(["success" => true, "message" => "Job posting rejected successfully! Reason: $reason"]);
            } elseif ($action === 'approve_employer') {
                $stmt = $conn->prepare("UPDATE employer_waitlist SET approval_status = 'Approved' WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->close();
                echo json_encode(["success" => true, "message" => "Employer approved successfully!"]);
            } elseif ($action === 'reject_employer' && isset($_POST['reason'])) {
                $reason = htmlspecialchars($_POST['reason']);
                $stmt = $conn->prepare("DELETE FROM employer_waitlist WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->close();
                echo json_encode(["success" => true, "message" => "Employer rejected successfully! Reason: $reason"]);
            } else {
                throw new Exception("Invalid action or missing parameters.");
            }
        } else {
            throw new Exception("Invalid request.");
        }
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
    exit();
}

// Fetch dashboard data
function getTotalUsers($conn) {
    $sql = "SELECT 
        (SELECT COUNT(*) FROM students) as students,
        (SELECT COUNT(*) FROM employers) as employers,
        (SELECT COUNT(*) FROM admins) as admins,
        (SELECT COUNT(*) FROM employer_waitlist WHERE approval_status = 'Pending') as pending_employers,
        (SELECT COUNT(*) FROM job_posting_waitlist WHERE approval_status = 'Pending') as pending_job_postings";
    
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

function getPendingEmployers($conn) {
    $sql = "SELECT * FROM employer_waitlist WHERE approval_status = 'Pending'";
    return $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}

function getPendingJobPostings($conn) {
    $sql = "SELECT jpw.*, e.company_name as employer_company 
            FROM job_posting_waitlist jpw
            JOIN employers e ON jpw.employer_id = e.id
            WHERE jpw.approval_status = 'Pending'";
    return $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}

$userStats = getTotalUsers($conn);
$pendingEmployers = getPendingEmployers($conn);
$pendingJobPostings = getPendingJobPostings($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <style>
    * {
          margin: 0;
          padding: 0;
          box-sizing: border-box;
          font-family: Arial, Helvetica, sans-serif;
      }

      body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            color: #333;
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

      /* Base styles */
        :root {
        --primary-color: #4361ee;
        --secondary-color: #3f37c9;
        --success-color: #4cc9f0;
        --danger-color: #f72585;
        --warning-color: #f8961e;
        --light-bg: #f8f9fa;
        --dark-text: #2b2d42;
        --medium-text: #495057;
        --light-text: #6c757d;
        --border-radius: 12px;
        --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        --transition: all 0.3s ease;
        }

        .admin-dashboard {
        max-width: 1400px;
        margin: 2rem auto;
        background-color: white;
        padding: 2rem;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        }

        h1 {
        color: var(--dark-text);
        text-align: center;
        margin-bottom: 2rem;
        font-weight: 700;
        font-size: 2.25rem;
        position: relative;
        padding-bottom: 0.75rem;
        }

        h1:after {
        content: '';
        position: absolute;
        width: 80px;
        height: 4px;
        background-color: var(--primary-color);
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        border-radius: 2px;
        }

        /* Dashboard Layout */
        .dashboard-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 2rem;
        }

        .dashboard-card {
        width: 100%;
        background-color: #fff;
        border-radius: var(--border-radius);
        padding: 1.5rem;
        box-shadow: var(--box-shadow);
        transition: var(--transition);
        }

        .dashboard-card:hover {
        transform: translateY(-5px);
        }

        .dashboard-card h2 {
        color: var(--dark-text);
        margin-top: 0;
        margin-bottom: 1.25rem;
        font-size: 1.5rem;
        border-bottom: 1px solid #eaeaea;
        padding-bottom: 0.75rem;
        }

        /* Stats Grid */
        .stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
        }

        .stat-item {
        background-color: var(--light-bg);
        border-radius: var(--border-radius);
        padding: 1.25rem;
        text-align: center;
        transition: var(--transition);
        border-left: 5px solid var(--primary-color);
        }

        .stat-item:nth-child(2) {
        border-left-color: var(--success-color);
        }

        .stat-item:nth-child(3) {
        border-left-color: var(--warning-color);
        }

        .stat-item:nth-child(4) {
        border-left-color: var(--danger-color);
        }

        .stat-item:hover {
        transform: scale(1.03);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .stat-item h3 {
        margin: 0 0 0.75rem;
        color: var(--medium-text);
        font-size: 1rem;
        font-weight: 600;
        }

        .stat-item p {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--dark-text);
        margin: 0;
        }

        /* Tables */
        .pending-employers-list,
        .pending-job-postings-list {
        width: 100%;
        overflow-x: auto;
        }

        .pending-employers-list table,
        .pending-job-postings-list table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-bottom: 1rem;
        }

        .pending-employers-list th,
        .pending-job-postings-list th,
        .pending-employers-list td,
        .pending-job-postings-list td {
        padding: 1rem;
        text-align: left;
        border: none;
        border-bottom: 1px solid #e9ecef;
        }

        .pending-employers-list th,
        .pending-job-postings-list th {
        background-color: #f8f9fa;
        color: var(--medium-text);
        font-weight: 600;
        position: sticky;
        top: 0;
        }

        .pending-employers-list tbody tr,
        .pending-job-postings-list tbody tr {
        transition: var(--transition);
        }

        .pending-employers-list tbody tr:last-child td,
        .pending-job-postings-list tbody tr:last-child td {
        border-bottom: none;
        }

        .pending-employers-list tbody tr:hover,
        .pending-job-postings-list tbody tr:hover {
        background-color: #f0f4f8;
        }

        /* Buttons */
        .btn {
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: var(--transition);
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        }

        .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-success {
        background-color: var(--success-color);
        color: white;
        }

        .btn-success:hover {
        background-color: #41b4da;
        }

        .btn-danger {
        background-color: var(--danger-color);
        color: white;
        }

        .btn-danger:hover {
        background-color: #e61376;
        }

        /* Alerts */
        .alert {
        padding: 1rem;
        margin-bottom: 1.5rem;
        border-radius: var(--border-radius);
        position: relative;
        border-left: 5px solid transparent;
        }

        .alert-success {
        background-color: #d4edda;
        color: #155724;
        border-left-color: #2ecc71;
        }

        .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border-left-color: #e74c3c;
        }

        /* Charts */
        #userChart {
        max-width: 100%;
        height: auto;
        margin: 1.5rem auto;
        display: block;
        }

        /* Responsive adjustments */
        @media (max-width: 1200px) {
        .admin-dashboard {
            margin: 1rem;
            padding: 1.5rem;
        }
        }

        @media (max-width: 768px) {
        .stat-grid {
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1rem;
        }
        
        .stat-item {
            padding: 1rem;
        }
        
        .stat-item p {
            font-size: 1.5rem;
        }
        
        h1 {
            font-size: 1.75rem;
        }
        
        .dashboard-card {
            padding: 1.25rem;
        }
        
        .dashboard-card h2 {
            font-size: 1.25rem;
        }
        }

        @media (max-width: 576px) {
        .admin-dashboard {
            margin: 0.5rem;
            padding: 1rem;
            border-radius: 8px;
        }
        
        .stat-grid {
            grid-template-columns: 1fr 1fr;
        }
        
        .btn {
            padding: 0.35rem 0.75rem;
            font-size: 0.875rem;
        }
        
        table th, table td {
            padding: 0.75rem;
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
    
    <div class="admin-dashboard">
        <h1>Admin Dashboard</h1>
        
        <div id="alert-container">
            <!-- Alerts will be inserted here dynamically -->
        </div>
        
        <div class="dashboard-grid">
            <!-- User Statistics -->
            <div class="dashboard-card">
                <h2>User Statistics</h2>
                <div class="stat-grid">
                    <div class="stat-item">
                        <h3>Students</h3>
                        <p><?php echo $userStats['students']; ?></p>
                    </div>
                    <div class="stat-item">
                        <h3>Employers</h3>
                        <p><?php echo $userStats['employers']; ?></p>
                    </div>
                    <div class="stat-item">
                        <h3>Admins</h3>
                        <p><?php echo $userStats['admins']; ?></p>
                    </div>
                    <div class="stat-item pending-employers">
                        <h3>Pending Employers</h3>
                        <p id="pending-employers-count"><?php echo $userStats['pending_employers']; ?></p>
                    </div>
                    <div class="stat-item pending-job-postings">
                        <h3>Pending Job Postings</h3>
                        <p id="pending-job-postings-count"><?php echo $userStats['pending_job_postings']; ?></p>
                    </div>
                </div>
                <canvas id="userChart"></canvas>
            </div>

            <!-- Pending Employers -->
            <div class="dashboard-card pending-employers-list">
                <h2>Pending Employer Approvals</h2>
                <?php if (empty($pendingEmployers)): ?>
                    <p id="no-pending-employers">No pending employer applications</p>
                <?php else: ?>
                    <table id="pending-employers-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Company</th>
                                <th>Email</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingEmployers as $employer): ?>
                                <tr data-employer-id="<?php echo $employer['id']; ?>">
                                    <td><?php echo htmlspecialchars($employer['fname'] . ' ' . $employer['lname']); ?></td>
                                    <td><?php echo htmlspecialchars($employer['company_name']); ?></td>
                                    <td><?php echo htmlspecialchars($employer['email']); ?></td>
                                    <td>
                                        <button class="btn btn-success approve-employer" data-id="<?php echo $employer['id']; ?>">Approve</button>
                                        <button class="btn btn-danger reject-employer" data-id="<?php echo $employer['id']; ?>">Reject</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- Pending Job Postings -->
            <div class="dashboard-card pending-job-postings-list">
                <h2>Pending Job Postings</h2>
                <?php if (empty($pendingJobPostings)): ?>
                    <p id="no-pending-job-postings">No pending job postings</p>
                <?php else: ?>
                    <table id="pending-job-postings-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Company</th>
                                <th>Location</th>
                                <th>Job Type</th>
                                <th>Subject</th>
                                <th>Salary</th>
                                <th>Deadline</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingJobPostings as $jobPosting): ?>
                                <tr data-job-id="<?php echo $jobPosting['id']; ?>">
                                    <td><?php echo htmlspecialchars($jobPosting['title']); ?></td>
                                    <td><?php echo htmlspecialchars($jobPosting['employer_company']); ?></td>
                                    <td><?php echo htmlspecialchars($jobPosting['location']); ?></td>
                                    <td><?php echo htmlspecialchars($jobPosting['job_type']); ?></td>
                                    <td><?php echo htmlspecialchars($jobPosting['job_subject']); ?></td>
                                    <td>$<?php echo number_format($jobPosting['salary'], 2); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($jobPosting['application_deadline'])); ?></td>
                                    <td>
                                        <div style="display:flex; gap:10px;">
                                            <button class="btn btn-success approve-job-posting" data-id="<?php echo $jobPosting['id']; ?>">Approve</button>
                                            <button class="btn btn-danger reject-job-posting" data-id="<?php echo $jobPosting['id']; ?>">Reject</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // User Chart
        const ctx = document.getElementById('userChart');
        let userChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Students', 'Employers', 'Admins', 'Pending Employers', 'Pending Job Postings'],
                datasets: [{
                    data: [
                        <?php echo $userStats['students']; ?>, 
                        <?php echo $userStats['employers']; ?>, 
                        <?php echo $userStats['admins']; ?>,
                        <?php echo $userStats['pending_employers']; ?>,
                        <?php echo $userStats['pending_job_postings']; ?>
                    ],
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'User Statistics'
                    }
                }
            }
        });

        // Show alert function
        function showAlert(message, type = 'success') {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.innerHTML = message;
            
            const alertContainer = document.getElementById('alert-container');
            alertContainer.innerHTML = '';
            alertContainer.appendChild(alertDiv);
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }

        // Update statistics function
        function updateStats(pendingEmployers, pendingJobPostings) {
            document.getElementById('pending-employers-count').textContent = pendingEmployers;
            document.getElementById('pending-job-postings-count').textContent = pendingJobPostings;
            
            // Update chart data
            userChart.data.datasets[0].data[3] = pendingEmployers;
            userChart.data.datasets[0].data[4] = pendingJobPostings;
            userChart.update();
        }

        // Handle employer approval
        document.querySelectorAll('.approve-employer').forEach(button => {
            button.addEventListener('click', function() {
                const employerId = this.getAttribute('data-id');
                const row = document.querySelector(`tr[data-employer-id="${employerId}"]`);
                
                // AJAX request
                const formData = new FormData();
                formData.append('action', 'approve_employer');
                formData.append('id', employerId);
                
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove the row
                        row.remove();
                        
                        // Update stats
                        const pendingEmployers = parseInt(document.getElementById('pending-employers-count').textContent) - 1;
                        updateStats(pendingEmployers, parseInt(document.getElementById('pending-job-postings-count').textContent));
                        
                        // Show success message
                        showAlert(data.message);
                        
                        // Check if table is empty
                        if (document.querySelectorAll('#pending-employers-table tbody tr').length === 0) {
                            document.getElementById('pending-employers-table').style.display = 'none';
                            const noEmployers = document.createElement('p');
                            noEmployers.id = 'no-pending-employers';
                            noEmployers.textContent = 'No pending employer applications';
                            document.querySelector('.pending-employers-list').appendChild(noEmployers);
                        }
                    } else {
                        showAlert(data.message, 'danger');
                    }
                })
                .catch(error => {
                    showAlert('An error occurred. Please try again.', 'danger');
                });
            });
        });

        // Handle job posting approval
        document.querySelectorAll('.approve-job-posting').forEach(button => {
            button.addEventListener('click', function() {
                const jobId = this.getAttribute('data-id');
                const row = document.querySelector(`tr[data-job-id="${jobId}"]`);
                
                // AJAX request
                const formData = new FormData();
                formData.append('action', 'approve_job_posting');
                formData.append('id', jobId);
                
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove the row
                        row.remove();
                        
                        // Update stats
                        const pendingJobPostings = parseInt(document.getElementById('pending-job-postings-count').textContent) - 1;
                        updateStats(parseInt(document.getElementById('pending-employers-count').textContent), pendingJobPostings);
                        
                        // Show success message
                        showAlert(data.message);
                        
                        // Check if table is empty
                        if (document.querySelectorAll('#pending-job-postings-table tbody tr').length === 0) {
                            document.getElementById('pending-job-postings-table').style.display = 'none';
                            const noJobs = document.createElement('p');
                            noJobs.id = 'no-pending-job-postings';
                            noJobs.textContent = 'No pending job postings';
                            document.querySelector('.pending-job-postings-list').appendChild(noJobs);
                        }
                    } else {
                        showAlert(data.message, 'danger');
                    }
                })
                .catch(error => {
                    showAlert('An error occurred. Please try again.', 'danger');
                });
            });
        });

        // Open rejection modal for employer
        document.querySelectorAll('.reject-employer').forEach(button => {
            button.addEventListener('click', function() {
                const employerId = this.getAttribute('data-id');
                document.getElementById('rejection-id').value = employerId;
                document.getElementById('rejection-type').value = 'employer';
                $('#rejectionModal').modal('show');
            });
        });

        // Open rejection modal for job posting
        document.querySelectorAll('.reject-job-posting').forEach(button => {
            button.addEventListener('click', function() {
                const jobId = this.getAttribute('data-id');
                document.getElementById('rejection-id').value = jobId;
                document.getElementById('rejection-type').value = 'job_posting';
                $('#rejectionModal').modal('show');
            });
        });

        // Handle rejection confirmation
        document.getElementById('confirm-rejection').addEventListener('click', function() {
            const id = document.getElementById('rejection-id').value;
            const type = document.getElementById('rejection-type').value;
            const reason = document.getElementById('rejection-reason').value;
            
            if (!reason.trim()) {
                alert('Please provide a reason for rejection');
                return;
            }
            
            const formData = new FormData();
            formData.append('id', id);
            formData.append('reason', reason);
            
            if (type === 'employer') {
                formData.append('action', 'reject_employer');
                const row = document.querySelector(`tr[data-employer-id="${id}"]`);
                
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove the row
                        row.remove();
                        
                        // Update stats
                        const pendingEmployers = parseInt(document.getElementById('pending-employers-count').textContent) - 1;
                        updateStats(pendingEmployers, parseInt(document.getElementById('pending-job-postings-count').textContent));
                        
                        // Show success message
                        showAlert(data.message);
                        
                        // Check if table is empty
                        if (document.querySelectorAll('#pending-employers-table tbody tr').length === 0) {
                            document.getElementById('pending-employers-table').style.display = 'none';
                            const noEmployers = document.createElement('p');
                            noEmployers.id = 'no-pending-employers';
                            noEmployers.textContent = 'No pending employer applications';
                            document.querySelector('.pending-employers-list').appendChild(noEmployers);
                        }
                        
                        $('#rejectionModal').modal('hide');
                    } else {
                        showAlert(data.message, 'danger');
                    }
                })
                .catch(error => {
                    showAlert('An error occurred. Please try again.', 'danger');
                });
            } else if (type === 'job_posting') {
                formData.append('action', 'reject_job_posting');
                const row = document.querySelector(`tr[data-job-id="${id}"]`);
                
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove the row
                        row.remove();
                        
                        // Update stats
                        const pendingJobPostings = parseInt(document.getElementById('pending-job-postings-count').textContent) - 1;
                        updateStats(parseInt(document.getElementById('pending-employers-count').textContent), pendingJobPostings);
                        
                        // Show success message
                        showAlert(data.message);
                        
                        // Check if table is empty
                        if (document.querySelectorAll('#pending-job-postings-table tbody tr').length === 0) {
                            document.getElementById('pending-job-postings-table').style.display = 'none';
                            const noJobs = document.createElement('p');
                            noJobs.id = 'no-pending-job-postings';
                            noJobs.textContent = 'No pending job postings';
                            document.querySelector('.pending-job-postings-list').appendChild(noJobs);
                        }
                        
                        $('#rejectionModal').modal('hide');
                    } else {
                        showAlert(data.message, 'danger');
                    }
                })
                .catch(error => {
                    showAlert('An error occurred. Please try again.', 'danger');
                });
            }
            
            // Clear the reason field
            document.getElementById('rejection-reason').value = '';
        });
    </script>

</body>
</html>