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
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: loginpage.php");
    exit();
}

// Handle job posting approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // New job posting approval handling
    if (isset($_POST['approve_job_posting'])) {
        $job_posting_id = $_POST['job_posting_id'];
        
        $conn->begin_transaction();
        
        try {
            // Fetch job posting details from waitlist
            $stmt = $conn->prepare("SELECT * FROM job_posting_waitlist WHERE id = ?");
            $stmt->bind_param("i", $job_posting_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $job_posting = $result->fetch_assoc();
            $stmt->close();
            
            if ($job_posting) {
                $stmt = $conn->prepare("INSERT INTO job_postings (
                    employer_id, company_name, title, description, 
                    requirements, salary, location, job_type, 
                    job_subject, application_deadline, approval_status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Approved')");
                
                $stmt->bind_param("issssdssss", 
                    $job_posting['employer_id'],
                    $job_posting['company_name'],
                    $job_posting['title'],
                    $job_posting['description'],
                    $job_posting['requirements'],
                    $job_posting['salary'],
                    $job_posting['location'],
                    $job_posting['job_type'],
                    $job_posting['job_subject'],
                    $job_posting['application_deadline']
                );
                

                $stmt->execute();
                $stmt->close();
                
                // Remove from waitlist
                $stmt = $conn->prepare("DELETE FROM job_posting_waitlist WHERE id = ?");
                $stmt->bind_param("i", $job_posting_id);
                $stmt->execute();
                $stmt->close();
                
                // Log the activity
                $log_description = "Job posting {$job_posting['title']} approved";
                $stmt = $conn->prepare("INSERT INTO system_logs (activity_type, description) VALUES ('job_posting_approval', ?)");
                $stmt->bind_param("s", $log_description);
                $stmt->execute();
                $stmt->close();
                
                $conn->commit();
                $_SESSION['notification'] = [
                    'type' => 'success',
                    'message' => 'Job posting approved successfully!',
                ];
            }
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['notification'] = [
                'type' => 'error',
                'message' => 'Error approving job posting: ' . $e->getMessage(),
            ];
        }
    }
    
    if (isset($_POST['reject_job_posting'])) {
        $job_posting_id = $_POST['job_posting_id'];
        
        $conn->begin_transaction();
        
        try {
            // Fetch job posting details for logging
            $stmt = $conn->prepare("SELECT title FROM job_posting_waitlist WHERE id = ?");
            $stmt->bind_param("i", $job_posting_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $job_posting = $result->fetch_assoc();
            $stmt->close();
            
            // Remove from waitlist
            $stmt = $conn->prepare("DELETE FROM job_posting_waitlist WHERE id = ?");
            $stmt->bind_param("i", $job_posting_id);
            $stmt->execute();
            $stmt->close();
            
            // Log the activity
            $log_description = "Job posting {$job_posting['title']} rejected";
            $stmt = $conn->prepare("INSERT INTO system_logs (activity_type, description) VALUES ('job_posting_rejection', ?)");
            $stmt->bind_param("s", $log_description);
            $stmt->execute();
            $stmt->close();
            
            $conn->commit();
            $_SESSION['notification'] = [
                'type' => 'success',
                'message' => 'Job posting rejected successfully!',
            ];
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['notification'] = [
                'type' => 'error',
                'message' => 'Error rejecting job posting: ' . $e->getMessage(),
            ];
        }
    }

    // Handle employer approval
    if (isset($_POST['approve_employer'])) {
        $employer_id = $_POST['employer_id'];
        
        $conn->begin_transaction();
        
        try {
            // Fetch employer details from waitlist
            $stmt = $conn->prepare("SELECT * FROM employer_waitlist WHERE id = ?");
            $stmt->bind_param("i", $employer_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $employer = $result->fetch_assoc();
            $stmt->close();
            
            if ($employer) {
                // Insert into employers table
                $stmt = $conn->prepare("INSERT INTO employers (
                    username, email, password, company_name, 
                    contact_name, contact_email, contact_phone, address, 
                    city, state, zip_code, description, website
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                $stmt->bind_param("sssssssssssss", 
                    $employer['username'],
                    $employer['email'],
                    $employer['password'],
                    $employer['company_name'],
                    $employer['contact_name'],
                    $employer['contact_email'],
                    $employer['contact_phone'],
                    $employer['address'],
                    $employer['city'],
                    $employer['state'],
                    $employer['zip_code'],
                    $employer['description'],
                    $employer['website']
                );
                $stmt->execute();
                $stmt->close();
                
                // Remove from waitlist
                $stmt = $conn->prepare("DELETE FROM employer_waitlist WHERE id = ?");
                $stmt->bind_param("i", $employer_id);
                $stmt->execute();
                $stmt->close();
                
                // Log the activity
                $log_description = "Employer {$employer['company_name']} approved";
                $stmt = $conn->prepare("INSERT INTO system_logs (activity_type, description) VALUES ('employer_approval', ?)");
                $stmt->bind_param("s", $log_description);
                $stmt->execute();
                $stmt->close();
                
                $conn->commit();
                $_SESSION['notification'] = [
                    'type' => 'success',
                    'message' => 'Employer approved successfully!',
                ];
            }
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['notification'] = [
                'type' => 'error',
                'message' => 'Error approving employer: ' . $e->getMessage(),
            ];
        }
    }
    
    if (isset($_POST['reject_employer'])) {
        $employer_id = $_POST['employer_id'];
        
        $conn->begin_transaction();
        
        try {
            // Fetch employer details for logging
            $stmt = $conn->prepare("SELECT company_name FROM employer_waitlist WHERE id = ?");
            $stmt->bind_param("i", $employer_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $employer = $result->fetch_assoc();
            $stmt->close();
            
            // Remove from waitlist
            $stmt = $conn->prepare("DELETE FROM employer_waitlist WHERE id = ?");
            $stmt->bind_param("i", $employer_id);
            $stmt->execute();
            $stmt->close();
            
            // Log the activity
            $log_description = "Employer {$employer['company_name']} rejected";
            $stmt = $conn->prepare("INSERT INTO system_logs (activity_type, description) VALUES ('employer_rejection', ?)");
            $stmt->bind_param("s", $log_description);
            $stmt->execute();
            $stmt->close();
            
            $conn->commit();
            $_SESSION['notification'] = [
                'type' => 'success',
                'message' => 'Employer rejected successfully!',
            ];
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['notification'] = [
                'type' => 'error',
                'message' => 'Error rejecting employer: ' . $e->getMessage(),
            ];
        }
    }

    // Redirect to prevent form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Function to get total user count
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

// Function to get pending employers
function getPendingEmployers($conn) {
    $sql = "SELECT * FROM employer_waitlist WHERE approval_status = 'Pending'";
    $result = $conn->query($sql);
    
    $pending_employers = [];
    while ($row = $result->fetch_assoc()) {
        $pending_employers[] = $row;
    }
    
    return $pending_employers;
}

// Function to get pending job postings
function getPendingJobPostings($conn) {
    $sql = "SELECT jpw.*, e.company_name as employer_company 
            FROM job_posting_waitlist jpw
            JOIN employers e ON jpw.employer_id = e.id
            WHERE jpw.approval_status = 'Pending'";
    $result = $conn->query($sql);
    
    $pending_job_postings = [];
    while ($row = $result->fetch_assoc()) {
        $pending_job_postings[] = $row;
    }
    
    return $pending_job_postings;
}

// Fetch dashboard data
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

      .admin-dashboard {
          max-width: 1200px;
          margin: 0 auto;
          background-color: white;
          padding: 30px;
          border-radius: 10px;
          box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      }
      h1 {
          color: #2c3e50;
          text-align: center;
          margin-bottom: 30px;
      }
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr;  /* Change to single column */
            gap: 20px;
        }

        .dashboard-card {
            width: 100%;  /* Ensure full width */
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));  /* Responsive grid */
            gap: 15px;
            margin-bottom: 20px;
        }
      .stat-item {
          background-color: #f8f9fa;
          border-radius: 6px;
          padding: 15px;
          text-align: center;
          transition: transform 0.3s ease;
      }
      .stat-item:hover {
          transform: scale(1.05);
      }
      .stat-item h3 {
          margin: 0 0 10px;
          color: #495057;
      }
      .stat-item p {
          font-size: 24px;
          font-weight: bold;
          color: #2c3e50;
          margin: 0;
      }
      .pending-employers-list table {
          width: 100%;
          border-collapse: collapse;
      }
      .pending-employers-list th, 
      .pending-employers-list td {
          border: 1px solid #e9ecef;
          padding: 10px;
          text-align: left;
      }
      .pending-employers-list th {
          background-color: #f1f3f5;
          color: #495057;
      }
      .btn {
          padding: 8px 12px;
          border: none;
          border-radius: 4px;
          cursor: pointer;
          transition: background-color 0.3s ease;
      }
      .btn-success {
          background-color: #2ecc71;
          color: white;
      }
      .btn-danger {
          background-color: #e74c3c;
          color: white;
      }
      .alert {
          padding: 15px;
          margin-bottom: 20px;
          border-radius: 4px;
      }
      .alert-success {
          background-color: #d4edda;
          color: #155724;
      }
      .alert-danger {
          background-color: #f8d7da;
          color: #721c24;
      }

        .pending-job-postings-list {
            width: 100%; /* Ensures full width of the container */
        }

        .pending-job-postings-list table {
            width: 100%;
            border-collapse: collapse;
        }

        .pending-job-postings-list th, 
        .pending-job-postings-list td {
            border: 1px solid #e9ecef;
            padding: 10px;
            text-align: left;
        }

        .pending-job-postings-list th {
            background-color: #f1f3f5;
            color: #495057;
            font-weight: 600;
        }

        .pending-job-postings-list tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .pending-job-postings-list tbody tr:hover {
            background-color: #e9ecef;
            transition: background-color 0.3s ease;
        }

        #userChart {
            max-width: 1000px;  /* Adjust this value as needed */
            max-height: 1000px; /* Adjust this value as needed */
            margin: 0 auto;    /* Center the chart */
            display: block;    /* Ensure it's a block-level element */
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
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
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
                        <p><?php echo $userStats['pending_employers']; ?></p>
                    </div>
                    <div class="stat-item pending-job-postings">
                        <h3>Pending Job Postings</h3>
                        <p><?php echo $userStats['pending_job_postings']; ?></p>
                    </div>
                </div>
                <canvas id="userChart"></canvas>
            </div>

            <!-- Pending Employers -->
            <div class="dashboard-card pending-employers-list">
                <h2>Pending Employer Approvals</h2>
                <?php if (empty($pendingEmployers)): ?>
                    <p>No pending employer applications</p>
                <?php else: ?>
                    <table>
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
                                <tr>
                                    <td><?php echo htmlspecialchars($employer['fname'] . ' ' . $employer['lname']); ?></td>
                                    <td><?php echo htmlspecialchars($employer['company_name']); ?></td>
                                    <td><?php echo htmlspecialchars($employer['email']); ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="employer_id" value="<?php echo $employer['id']; ?>">
                                            <button type="submit" name="approve_employer" class="btn btn-success">Approve</button>
                                            <button type="submit" name="reject_employer" class="btn btn-danger">Reject</button>
                                        </form>
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
                    <p>No pending job postings</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Company</th>
                                <th>Location</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingJobPostings as $jobPosting): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($jobPosting['title']); ?></td>
                                    <td><?php echo htmlspecialchars($jobPosting['employer_company']); ?></td>
                                    <td><?php echo htmlspecialchars($jobPosting['location']); ?></td>
                                    <td>
                                        <form method="POST" style="display:flex; gap:10px;">
                                            <input type="hidden" name="job_posting_id" value="<?php echo $jobPosting['id']; ?>">
                                            <button type="submit" name="approve_job_posting" class="btn btn-success">Approve</button>
                                            <button type="submit" name="reject_job_posting" class="btn btn-danger">Reject</button>
                                        </form>
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
        new Chart(ctx, {
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
    </script>

</body>
</html>