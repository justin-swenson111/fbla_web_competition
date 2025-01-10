<?php
session_start();

$servername = "localhost";
$username = "root";
$password = ""; 
$dbname = "fbla";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check if user is logged in as a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header("Location: login.php");
    exit();
}

$isLoggedIn = isset($_SESSION['user_id']);
$userType = $_SESSION['user_type'] ?? null;

// Get student's first name
$stmt = $conn->prepare("SELECT fname FROM students WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $_SESSION['fname'] = $row['fname'];
}

// Handle AJAX request for applications
if (isset($_GET['get_applications'])) {
    header('Content-Type: application/json');
    
    // Pagination parameters
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;
    
    // Get total count for pagination
    $total_query = "SELECT COUNT(*) as count FROM job_applications WHERE student_id = ?";
    $total_stmt = $conn->prepare($total_query);
    $total_stmt->bind_param("i", $_SESSION['user_id']);
    $total_stmt->execute();
    $total_result = $total_stmt->get_result();
    $total_row = $total_result->fetch_assoc();
    $total_records = $total_row['count'];
    $total_pages = ceil($total_records / $limit);
    
    // Get applications with pagination
    $apps_sql = "SELECT ja.*, jp.title, jp.company_name
                FROM job_applications ja 
                JOIN job_postings jp ON ja.job_posting_id = jp.id 
                WHERE ja.student_id = ? 
                ORDER BY ja.applied_at DESC
                LIMIT ? OFFSET ?";
    
    $apps_stmt = $conn->prepare($apps_sql);
    $apps_stmt->bind_param("iii", $_SESSION['user_id'], $limit, $offset);
    $apps_stmt->execute();
    $apps_result = $apps_stmt->get_result();
    
    $applications = [];
    while ($app = $apps_result->fetch_assoc()) {
        $applications[] = $app;
    }
    
    echo json_encode([
        'applications' => $applications,
        'total_pages' => $total_pages,
        'current_page' => $page
    ]);
    exit();
}

// Handle AJAX request for filtered jobs
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    
    // Pagination parameters
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;
    
    // Get filter parameters
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $location = isset($_GET['location']) ? $_GET['location'] : '';
    $salary_range = isset($_GET['salary']) ? $_GET['salary'] : '';
    $job_type = isset($_GET['jobType']) ? $_GET['jobType'] : '';

    // Build the query
    $query = "SELECT SQL_CALC_FOUND_ROWS * FROM job_postings WHERE is_active = 1";
    $params = [];
    $types = "";

    if ($search) {
        $query .= " AND (title LIKE ? OR description LIKE ?)";
        $search_param = "%$search%";
        array_push($params, $search_param, $search_param);
        $types .= "ss";
    }

    if ($location) {
        $query .= " AND location = ?";
        array_push($params, $location);
        $types .= "s";
    }

    if ($job_type) {
        $query .= " AND job_type = ?";
        array_push($params, $job_type);
        $types .= "s";
    }

    if ($salary_range) {
        $salary_parts = explode('-', $salary_range);
        if (count($salary_parts) == 2) {
            $query .= " AND salary BETWEEN ? AND ?";
            array_push($params, (float)$salary_parts[0], (float)$salary_parts[1]);
            $types .= "dd";
        } elseif (strpos($salary_range, '+') !== false) {
            $query .= " AND salary >= ?";
            array_push($params, (float)str_replace('+', '', $salary_range));
            $types .= "d";
        }
    }

    $query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
    array_push($params, $limit, $offset);
    $types .= "ii";

    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    
    // Get total number of records for pagination
    $total_result = $conn->query("SELECT FOUND_ROWS()");
    $total_row = $total_result->fetch_row();
    $total_records = $total_row[0];
    $total_pages = ceil($total_records / $limit);
    
    $jobs = [];

    while ($row = $result->fetch_assoc()) {
        $check_sql = "SELECT id FROM job_applications WHERE job_posting_id = ? AND student_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $row['id'], $_SESSION['user_id']);
        $check_stmt->execute();
        $already_applied = $check_stmt->get_result()->num_rows > 0;
        
        $row['already_applied'] = $already_applied;
        $row['formatted_salary'] = number_format($row['salary'], 2);
        $row['formatted_deadline'] = date('M d, Y', strtotime($row['application_deadline']));
        
        $jobs[] = $row;
    }

    echo json_encode([
        'jobs' => $jobs,
        'total_pages' => $total_pages,
        'current_page' => $page
    ]);
    exit();
}

// Fetch distinct locations for the filter
$locations_query = "SELECT DISTINCT location FROM job_postings WHERE is_active = 1 ORDER BY location";
$locations_result = $conn->query($locations_query);

if (!$locations_result) {
    echo "Error fetching locations: " . $conn->error;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="navbar-responsive.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 100%; /* Ensure container doesn't cause overflow */
            margin: 0 auto;
            padding: 20px;
        }

        .filters {
            margin: 20px 0;
            padding: 15px;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .search-bar {
            flex: 1;
            min-width: 200px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            min-width: 150px;
        }       

        .job-posting {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .job-posting h3 {
            margin-top: 0;
            color: #007bff;
        }

        .job-details {
            margin: 15px 0;
        }

        .application-form {
            margin-top: 15px;
        }

        .application-form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            resize: vertical;
            min-height: 100px;
            margin-bottom: 10px;
        }

        .file-upload {
            margin: 10px 0;
        }

        .apply-button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .apply-button:hover {
            background-color: #218838;
        }

        .apply-button:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }

        .no-jobs {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .job-meta {
            display: flex;
            gap: 20px;
            color: #666;
            font-size: 0.9em;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }

        .pagination a {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-decoration: none;
            color: #007bff;
        }

        .pagination a.active {
            background-color: #007bff;
            color: white;
        }

        .my-applications {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .application-status {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9em;
            display: inline-block;
        }

        .status-pending {
            background-color: #ffeeba;
            color: #856404;
        }

        .status-accepted {
            background-color: #d4edda;
            color: #155724;
        }

        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }

        .loading {
            text-align: center;
            padding: 20px;
            font-style: italic;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="navbar">
      <!-- Logo -->
      <a href="index.php">
          <img
              src="./media/logo.png"
              alt="Logo"
              class="logo"
              height="200px"
              width="auto"
          />
      </a>

      <!-- Navigation Links -->
      <div class="nav-links">
          <a href="aboutUs.php">About Us</a>
          <a href="resources.php">Resources</a>

          <!-- Display Login Link if Not Logged In -->
          <?php if (!$isLoggedIn): ?>
              <a href="./loginpage.php">Login</a>
          <?php endif; ?>

          <!-- Display Links for Logged-In Users -->
          <?php if ($isLoggedIn): ?>
              <!-- Show Student Dashboard if user is a student -->
              <?php if ($_SESSION['user_type'] === 'student'): ?>
                  <a href="studentdash.php">Student Dashboard</a>
              <?php endif; ?>

              <!-- Show Employer Dashboard if user is an employer -->
              <?php if ($_SESSION['user_type'] === 'employer'): ?>
                  <a href="employerdash.php">Employer Dashboard</a>
              <?php endif; ?>

              <!-- Profile/Settings Link -->
              <a href="settings.php" class="profile-link">
                  <img
                      src="./media/socialimage2.jpg"
                      alt="Profile"
                      class="profile-pic"
                  />
              </a>
          <?php endif; ?>
      </div>
    </div>


    <br>
    <br>
    <br>

    <div class="container">
        <!-- Filters Section -->
        <div class="filters">
            <input type="text" class="search-bar" placeholder="Search for jobs..." id="searchJobs">
            <select id="locationFilter">
                <option value="">All Locations</option>
                <?php
                if ($locations_result) {
                    while ($loc = $locations_result->fetch_assoc()) {
                        echo "<option value='" . htmlspecialchars($loc['location']) . "'>" . 
                            htmlspecialchars($loc['location']) . "</option>";
                    }
                }
                ?>
            </select>
            <select id="salaryFilter">
                <option value="">All Salaries</option>
                <option value="0-40000">Under $40,000</option>
                <option value="40000-80000">$40,000 - $80,000</option>
                <option value="80000+">$80,000+</option>
            </select>
            <select id="jobTypeFilter">
                <option value="">All Job Types</option>
                <option value="Full-time">Full-time</option>
                <option value="Part-time">Part-time</option>
                <option value="Contract">Contract</option>
                <option value="Internship">Internship</option>
            </select>
        </div>

        <!-- My Applications Section -->
        <div class="my-applications">
            <h2>My Applications</h2>
            <?php
            $apps_sql = "SELECT ja.*, jp.title, jp.company_name
                        FROM job_applications ja 
                        JOIN job_postings jp ON ja.job_posting_id = jp.id 
                        WHERE ja.student_id = ? 
                        ORDER BY ja.applied_at DESC";
            $apps_stmt = $conn->prepare($apps_sql);
            $apps_stmt->bind_param("i", $_SESSION['user_id']);
            $apps_stmt->execute();
            $apps_result = $apps_stmt->get_result();

            if ($apps_result->num_rows > 0) {
                while ($app = $apps_result->fetch_assoc()) {
                    $status_class = "status-" . strtolower($app['status']);
                    echo "<div class='job-posting'>";
                    echo "<h3>" . htmlspecialchars($app['title']) . " at " . htmlspecialchars($app['company_name']) . "</h3>";
                    echo "<span class='application-status " . $status_class . "'>" . 
                        ucfirst(htmlspecialchars($app['status'])) . "</span>";
                    echo "<p>Applied on: " . date('M d, Y', strtotime($app['applied_at'])) . "</p>";
                    echo "</div>";
                }
            } else {
                echo "<p>You haven't applied to any jobs yet.</p>";
            }
            ?>
        </div>

        <!-- Available Job Listings -->
        <div id="job-list">
            <div class="loading">Loading jobs...</div>
        </div>
    </div>

    <script>
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Add the new handleJobApplication function
        function handleJobApplication(form, jobId) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(form);
                
                fetch('apply_job.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove the job card
                        const jobCard = form.closest('.job-posting');
                        jobCard.style.opacity = '0';
                        setTimeout(() => {
                            jobCard.remove();
                        }, 300);
                        
                        // Refresh the applications section
                        fetchApplications(1);
                        
                        // Show success message
                        alert('Application submitted successfully!');
                    } else {
                        alert(data.message || 'Error submitting application');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error submitting application');
                });
            });
        }

    // Add the new fetchApplications function
        function handleJobApplication(form, jobId) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(form);
                const jobCard = form.closest('.job-posting');
                
                fetch('apply_job.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Add fade-out animation
                        jobCard.style.transition = 'opacity 0.3s ease-out';
                        jobCard.style.opacity = '0';
                        
                        // Remove the job card after animation
                        setTimeout(() => {
                            jobCard.remove();
                        }, 300);
                        
                        // Refresh the applications section
                        fetchApplications(1);
                        
                        // Show success message
                        alert('Application submitted successfully!');
                        
                        // Refresh remaining jobs list to ensure proper pagination
                        fetchFilteredJobs(1);
                    } else {
                        alert(data.message || 'Error submitting application');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error submitting application');
                });
            });
        }

        function handleJobApplication(form, jobId) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(form);
                const jobCard = form.closest('.job-posting');
                
                fetch('apply_job.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Add fade-out animation
                        jobCard.style.transition = 'opacity 0.3s ease-out';
                        jobCard.style.opacity = '0';
                        
                        // Remove the job card after animation
                        setTimeout(() => {
                            jobCard.remove();
                        }, 300);
                        
                        // Refresh the applications section
                        fetchApplications(1);
                        
                        // Show success message
                        alert('Application submitted successfully!');
                        
                        // Get current page before refreshing
                        const currentPage = getCurrentPageFromPagination('job-list');
                        
                        // Refresh remaining jobs list to ensure proper pagination
                        fetchFilteredJobs(currentPage);
                    } else {
                        alert(data.message || 'Error submitting application');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error submitting application');
                });
            });
        }

        function getCurrentPageFromPagination(containerId) {
            const container = document.getElementById(containerId);
            const activePage = container.querySelector('.pagination .active');
            return activePage ? parseInt(activePage.textContent) : 1;
        }

        function createPaginationControls(currentPage, totalPages, onPageClick) {
            if (totalPages <= 1) return '';
            
            let paginationHtml = '<div class="pagination">';
            
            // Previous button
            paginationHtml += `
                <a href="#" 
                onclick="${onPageClick}(${Math.max(1, currentPage - 1)}); return false;"
                class="${currentPage === 1 ? 'disabled' : ''}"
                ${currentPage === 1 ? 'disabled="disabled"' : ''}>
                Previous
                </a>
            `;

            // Calculate range of pages to show
            let startPage = Math.max(1, currentPage - 2);
            let endPage = Math.min(totalPages, currentPage + 2);

            // Adjust range if at edges
            if (startPage <= 3) {
                endPage = Math.min(5, totalPages);
                startPage = 1;
            }
            if (endPage >= totalPages - 2) {
                startPage = Math.max(1, totalPages - 4);
                endPage = totalPages;
            }

            // First page and ellipsis
            if (startPage > 1) {
                paginationHtml += `
                    <a href="#" onclick="${onPageClick}(1); return false;">1</a>
                    ${startPage > 2 ? '<span class="ellipsis">...</span>' : ''}
                `;
            }

            // Page numbers
            for (let i = startPage; i <= endPage; i++) {
                paginationHtml += `
                    <a href="#" 
                    onclick="${onPageClick}(${i}); return false;"
                    class="${i === currentPage ? 'active' : ''}">${i}</a>
                `;
            }

            // Last page and ellipsis
            if (endPage < totalPages) {
                paginationHtml += `
                    ${endPage < totalPages - 1 ? '<span class="ellipsis">...</span>' : ''}
                    <a href="#" onclick="${onPageClick}(${totalPages}); return false;">${totalPages}</a>
                `;
            }

            // Next button
            paginationHtml += `
                <a href="#" 
                onclick="${onPageClick}(${Math.min(totalPages, currentPage + 1)}); return false;"
                class="${currentPage === totalPages ? 'disabled' : ''}"
                ${currentPage === totalPages ? 'disabled="disabled"' : ''}>
                Next
                </a>
            `;

            paginationHtml += '</div>';
            return paginationHtml;
        }

        function fetchFilteredJobs(page = 1) {
            const jobList = document.getElementById('job-list');
            jobList.innerHTML = '<div class="loading">Loading jobs...</div>';

            const searchText = document.getElementById('searchJobs').value;
            const locationFilter = document.getElementById('locationFilter').value;
            const salaryFilter = document.getElementById('salaryFilter').value;
            const jobTypeFilter = document.getElementById('jobTypeFilter').value;
            
            const params = new URLSearchParams({
                ajax: true,
                page: page,
                search: searchText,
                location: locationFilter,
                salary: salaryFilter,
                jobType: jobTypeFilter
            });
            
            fetch(`?${params}`)
                .then(response => response.json())
                .then(data => {
                    let html = '';
                    if (data.jobs.length === 0) {
                        html = "<div class='no-jobs'>No job postings match your filters.</div>";
                    } else {
                        // Filter out jobs that the user has already applied to
                        const availableJobs = data.jobs.filter(job => !job.already_applied);
                        
                        if (availableJobs.length === 0) {
                            html = "<div class='no-jobs'>You have applied to all available jobs matching your filters.</div>";
                        } else {
                            html = availableJobs.map(job => `
                                <div class="job-posting">
                                    <h3>${escapeHtml(job.title)} at ${escapeHtml(job.company_name)}</h3>
                                    <div class="job-meta">
                                        <div class="meta-item">
                                            <span>💰 $${job.formatted_salary}</span>
                                        </div>
                                        <div class="meta-item">
                                            <span>📍 ${escapeHtml(job.location)}</span>
                                        </div>
                                        <div class="meta-item">
                                            <span>🕒 ${escapeHtml(job.job_type)}</span>
                                        </div>
                                        <div class="meta-item">
                                            <span>📅 Deadline: ${job.formatted_deadline}</span>
                                        </div>
                                    </div>
                                    <div class="job-details">
                                        <p><strong>Description:</strong><br>
                                        ${escapeHtml(job.description).replace(/\n/g, '<br>')}</p>
                                        
                                        <p><strong>Requirements:</strong><br>
                                        ${escapeHtml(job.requirements).replace(/\n/g, '<br>')}</p>
                                    </div>

                                    <form class="application-form" action="apply_job.php" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="job_posting_id" value="${job.id}">
                                        <textarea name="cover_letter" 
                                                placeholder="Write your application message here... Include why you're interested in this position and what makes you a good fit."
                                                required></textarea>
                                        <div class="file-upload">
                                            <label for="resume">Upload Resume (PDF, DOC, DOCX):</label>
                                            <input type="file" name="resume" id="resume" accept=".pdf,.doc,.docx" required>
                                        </div>
                                        <button type="submit" class="apply-button">Apply Now</button>
                                    </form>
                                </div>
                            `).join('');
                        }
                        
                        // Add enhanced pagination controls
                        html += createPaginationControls(data.current_page, data.total_pages, 'fetchFilteredJobs');
                    }
                    
                    jobList.innerHTML = html;
                    attachFormListeners();
                })
                .catch(error => {
                    console.error('Error:', error);
                    jobList.innerHTML = "<div class='no-jobs'>An error occurred while fetching jobs.</div>";
                });
        }

        function fetchApplications(page = 1) {
            fetch(`?get_applications=true&page=${page}`)
                .then(response => response.json())
                .then(data => {
                    const applicationsDiv = document.querySelector('.my-applications');
                    let html = '<h2>My Applications</h2>';
                    
                    if (data.applications.length > 0) {
                        data.applications.forEach(app => {
                            const statusClass = `status-${app.status.toLowerCase()}`;
                            html += `
                                <div class='job-posting'>
                                    <h3>${escapeHtml(app.title)} at ${escapeHtml(app.company_name)}</h3>
                                    <span class='application-status ${statusClass}'>
                                        ${app.status.charAt(0).toUpperCase() + app.status.slice(1)}
                                    </span>
                                    <p>Applied on: ${new Date(app.applied_at).toLocaleDateString()}</p>
                                </div>
                            `;
                        });
                        
                        // Add enhanced pagination controls for applications
                        html += createPaginationControls(data.current_page, data.total_pages, 'fetchApplications');
                    } else {
                        html += "<p>You haven't applied to any jobs yet.</p>";
                    }
                    
                    applicationsDiv.innerHTML = html;
                });
        }
        
        function escapeHtml(unsafe) {
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        function attachFormListeners() {
            document.querySelectorAll('.application-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    const message = this.querySelector('textarea').value.trim();
                    const resume = this.querySelector('input[type="file"]').files[0];
                    
                    if (!message) {
                        e.preventDefault();
                        alert('Please write an application message.');
                        return;
                    }

                    if (!resume) {
                        e.preventDefault();
                        alert('Please upload your resume.');
                        return;
                    }

                    const allowedTypes = ['.pdf', '.doc', '.docx'];
                    const fileExtension = resume.name.toLowerCase().substring(resume.name.lastIndexOf('.'));
                    if (!allowedTypes.includes(fileExtension)) {
                        e.preventDefault();
                        alert('Please upload a PDF, DOC, or DOCX file.');
                        return;
                    }

                    const button = this.querySelector('button');
                    button.disabled = true;
                    button.innerHTML = '<span>Submitting...</span>';
                });
            });
        }

        // Add debounced event listeners to filters
        const debouncedFetch = debounce(fetchFilteredJobs, 300);

        document.getElementById('searchJobs').addEventListener('input', debouncedFetch);
        document.getElementById('locationFilter').addEventListener('change', fetchFilteredJobs);
        document.getElementById('salaryFilter').addEventListener('change', fetchFilteredJobs);
        document.getElementById('jobTypeFilter').addEventListener('change', fetchFilteredJobs);

        // Initial load
        fetchFilteredJobs(1);
        fetchApplications(1);
    </script>
</body>
</html>