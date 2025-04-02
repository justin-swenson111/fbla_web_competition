<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "mysql"; 
$dbname = "fbla";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check if user is logged in as a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header("Location: login.php");
    exit();
}

$isLoggedIn = isset($_SESSION['user_id']);
$userType = $_SESSION['user_type'] ?? null;

// Function to fetch scholarships from multiple sources
function fetchScholarships($search = '', $subject = '', $amount_range = '') {
    // Hardcoded scholarships from original source
    $hardcoded_scholarships = [
        [
            'id' => 1,
            'title' => 'STEM Innovation Scholarship',
            'description' => 'Scholarship for students pursuing innovative STEM research',
            'subject' => 'STEM',
            'award_amount' => 5000,
            'deadline' => '2024-09-15',
            'external_link' => 'https://www.scholarships.com/stem-innovation-scholarship',
            'grade_level' => 'All Grade Levels'
        ],
        [
            'id' => 2,
            'title' => 'Business Leadership Grant',
            'description' => 'Supporting future business leaders in their academic journey',
            'subject' => 'Business',
            'award_amount' => 3000,
            'deadline' => '2024-08-30',
            'external_link' => 'https://www.scholarships.com/business-leadership-grant',
            'grade_level' => 'College Students'
        ],
        [
            'id' => 3,
            'title' => 'Arts and Creativity Scholarship',
            'description' => 'Empowering creative students in their artistic pursuits',
            'subject' => 'Arts',
            'award_amount' => 2500,
            'deadline' => '2024-10-01',
            'external_link' => 'https://www.scholarships.com/arts-creativity-scholarship',
            'grade_level' => 'All Grade Levels'
        ]
    ];

    // Load scholarships from JSON file
    $json_file = 'scholarships.json';
    $json_scholarships = [];
    if (file_exists($json_file)) {
        $json_content = file_get_contents($json_file);
        $json_scholarships = json_decode($json_content, true);
        
        // Transform JSON scholarships to match the existing schema
        $json_scholarships = array_map(function($scholarship) {
            return [
                'id' => uniqid(), // Generate a unique ID
                'title' => $scholarship['title'],
                'description' => 'No additional description available',
                'subject' => 'Unspecified', // We'll need to map this if possible
                'award_amount' => (int)str_replace(['$', ','], '', $scholarship['amount']),
                'deadline' => $scholarship['deadline'],
                'external_link' => $scholarship['link'] ?: '#',
                'grade_level' => $scholarship['grade_level']
            ];
        }, $json_scholarships);
    }

    // Combine scholarships
    $all_scholarships = array_merge($hardcoded_scholarships, $json_scholarships);

    // Simple filtering logic
    $filtered_scholarships = array_filter($all_scholarships, function($scholarship) use ($search, $subject, $amount_range) {
        // Filter by search term
        if ($search && 
            stripos($scholarship['title'], $search) === false && 
            stripos($scholarship['description'], $search) === false) {
            return false;
        }

        // Filter by subject (with more flexible matching)
        if ($subject) {
            $subject_match = false;
            $subject_mapping = [
                'STEM' => ['STEM', 'Science', 'Technology', 'Engineering', 'Mathematics'],
                'Business' => ['Business', 'Entrepreneurship', 'Leadership'],
                'Arts' => ['Arts', 'Creative', 'Design', 'Music'],
                'Humanities' => ['Humanities', 'Social Sciences', 'Literature']
            ];

            if (isset($subject_mapping[$subject])) {
                foreach ($subject_mapping[$subject] as $possible_subject) {
                    if (stripos($scholarship['title'], $possible_subject) !== false || 
                        stripos($scholarship['subject'], $possible_subject) !== false) {
                        $subject_match = true;
                        break;
                    }
                }
            }

            if ($subject && !$subject_match) {
                return false;
            }
        }

        // Filter by amount range
        if ($amount_range) {
            $amount_parts = explode('-', $amount_range);
            if (count($amount_parts) == 2) {
                if ($scholarship['award_amount'] < $amount_parts[0] || 
                    $scholarship['award_amount'] > $amount_parts[1]) {
                    return false;
                }
            } elseif (strpos($amount_range, '+') !== false) {
                $min_amount = (float)str_replace('+', '', $amount_range);
                if ($scholarship['award_amount'] < $min_amount) {
                    return false;
                }
            }
        }

        return true;
    });

    return $filtered_scholarships;
}

// Handle AJAX request for scholarships
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    
    $search = $_GET['search'] ?? '';
    $subject = $_GET['subject'] ?? '';
    $amount_range = $_GET['amount'] ?? '';

    $scholarships = fetchScholarships($search, $subject, $amount_range);

    echo json_encode([
        'scholarships' => $scholarships,
        'total_pages' => 1,
        'current_page' => 1
    ]);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>External Scholarships</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
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

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 2rem;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .pagination button {
            background-color: #f57f17;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            min-width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pagination button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }

        .pagination button:hover:not(:disabled) {
            background-color: #e67e22;
        }

        .pagination #current-page {
            font-weight: bold;
            color: #2c3e50;
            padding: 0 0.5rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
            background-color: #f4f4f4;
            width: 100%;
            box-sizing: border-box;
        }

        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 2rem;
            font-size: clamp(1.8rem, 5vw, 2.5rem);
            border-bottom: 3px solid #f57f17;
            padding-bottom: 0.5rem;
        }

        .filters {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            gap: 1rem;
        }

        .filters input,
        .filters select {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            transition: all 0.3s ease;
            min-height: 44px;
        }

        .filters input:focus,
        .filters select:focus {
            outline: none;
            border-color: #f57f17;
            box-shadow: 0 0 5px rgba(245, 127, 23, 0.3);
        }

        #scholarships-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .scholarship-card {
            background-color: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .scholarship-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .scholarship-card h3 {
            color: #2c3e50;
            margin-bottom: 1rem;
            font-size: 1.25rem;
            border-bottom: 2px solid #f57f17;
            padding-bottom: 0.5rem;
        }

        .scholarship-card p {
            color: #34495e;
            margin-bottom: 1rem;
            line-height: 1.6;
            flex-grow: 1;
        }

        .scholarship-details {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-bottom: 1rem;
            color: #7f8c8d;
        }

        .scholarship-details span {
            font-size: 0.9rem;
        }

        .btn {
            display: inline-block;
            width: 100%;
            padding: 0.75rem;
            background-color: #f57f17;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
            min-height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn:hover {
            background-color: #e67e22;
        }

        /* Responsive Adjustments */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 2rem;
            gap: 1rem;
            flex-wrap: wrap; /* Allows pagination to wrap on small screens */
        }

        .pagination button {
            background-color: #f57f17;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .pagination button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }

        .pagination button:hover:not(:disabled) {
            background-color: #e67e22;
        }

        .pagination #current-page {
            font-weight: bold;
            color: #2c3e50;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            background-color: #f4f4f4;
            width: 100%;
            box-sizing: border-box; /* Ensures padding doesn't increase width */
        }

        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2.5rem;
            border-bottom: 3px solid #f57f17;
            padding-bottom: 0.5rem;
        }

        .filters {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            gap: 1rem;
        }

        .filters input,
        .filters select {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .filters input:focus,
        .filters select:focus {
            outline: none;
            border-color: #f57f17;
            box-shadow: 0 0 5px rgba(245, 127, 23, 0.3);
        }

        #scholarships-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .scholarship-card {
            background-color: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .scholarship-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .scholarship-card h3 {
            color: #2c3e50;
            margin-bottom: 1rem;
            font-size: 1.25rem;
            border-bottom: 2px solid #f57f17;
            padding-bottom: 0.5rem;
        }

        .scholarship-card p {
            color: #34495e;
            margin-bottom: 1rem;
            line-height: 1.6;
        }

        .scholarship-details {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-bottom: 1rem;
            color: #7f8c8d;
        }

        .scholarship-details span {
            font-size: 0.9rem;
        }

        .btn {
            display: inline-block;
            width: 100%;
            padding: 0.75rem;
            background-color: #f57f17;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #e67e22;
        }

        /* Large Screens (1200px and up) */
        @media screen and (max-width: 1200px) {
            #scholarships-list {
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); /* More flexible grid */
            }
        }

        /* Medium Screens (992px and up) */
        @media screen and (max-width: 992px) {
            #scholarships-list {
                grid-template-columns: repeat(2, minmax(280px, 1fr)); /* Two columns */
            }

            .container {
                padding: 2rem 1.5rem;
            }
        }

        /* Tablets (768px and up) */
        @media screen and (max-width: 768px) {
            .filters {
                flex-direction: column;
                align-items: stretch; /* Ensures filters stack properly */
            }

            #scholarships-list {
                grid-template-columns: repeat(1, minmax(280px, 1fr)); /* One column */
            }

            h1 {
                font-size: 2rem;
            }

            .container {
                padding: 1.5rem 1rem;
            }
        }

        /* Mobile (576px and up) */
        @media screen and (max-width: 576px) {
            .scholarship-card {
                padding: 1.25rem;
            }

            h1 {
                font-size: 1.75rem;
            }

            .pagination {
                gap: 0.75rem;
            }
        }

        /* Small Mobile (480px and up) */
        @media screen and (max-width: 480px) {
            .container {
                padding: 1rem;
            }

            h1 {
                margin-bottom: 1.5rem;
                font-size: 1.5rem;
            }

            .pagination {
                gap: 0.5rem;
            }

            .pagination button {
                padding: 0.4rem 0.8rem;
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


    <div class="container">
        <h1>External Scholarships</h1>
        
        <div class="filters">
            <input type="text" id="search" placeholder="Search scholarships">
            <select id="amount">
                <option value="">All Amounts</option>
                <option value="0-1000">$0 - $1,000</option>
                <option value="1000-5000">$1,000 - $5,000</option>
                <option value="5000+">$5,000+</option>
            </select>
        </div>
        
        <div id="scholarships-list">
            <!-- Scholarships will be dynamically loaded here -->
        </div>

        <div class="pagination">
            <button id="prev-page" disabled>Previous</button>
            <span id="current-page">Page 1</span>
            <button id="next-page" disabled>Next</button>
        </div>
    </div>

    <script>
    let scholarships = []; // Global variable to store all scholarships
    let filteredScholarships = []; // Variable to store filtered scholarships
    const ITEMS_PER_PAGE = 6;
    let currentPage = 1;

    // Fetch scholarships from JSON file
    async function loadScholarships() {
        try {
            const response = await fetch('scholarships.json');
            scholarships = await response.json();
            filteredScholarships = [...scholarships];
            renderScholarships();
            setupPagination();
        } catch (error) {
            console.error('Error loading scholarships:', error);
        }
    }

    // Filter scholarships based on search and amount
    function filterScholarships() {
        const searchTerm = document.getElementById('search').value.toLowerCase();
        const amountFilter = document.getElementById('amount').value;

        filteredScholarships = scholarships.filter(scholarship => {
            // Search filter
            const matchesSearch = scholarship.title.toLowerCase().includes(searchTerm);

            // Amount filter
            const amount = parseFloat(scholarship.amount.replace('$', '').replace(',', ''));
            let matchesAmount = true;
            switch(amountFilter) {
                case '0-1000':
                    matchesAmount = amount >= 0 && amount <= 1000;
                    break;
                case '1000-5000':
                    matchesAmount = amount >= 1000 && amount <= 5000;
                    break;
                case '5000+':
                    matchesAmount = amount >= 5000;
                    break;
            }

            return matchesSearch && matchesAmount;
        });

        currentPage = 1;
        renderScholarships();
        setupPagination();
    }

    // Render scholarships for current page
    function renderScholarships() {
        const scholarshipsList = document.getElementById('scholarships-list');
        scholarshipsList.innerHTML = '';

        const startIndex = (currentPage - 1) * ITEMS_PER_PAGE;
        const endIndex = startIndex + ITEMS_PER_PAGE;
        const pageScholarships = filteredScholarships.slice(startIndex, endIndex);

        pageScholarships.forEach(scholarship => {
            const scholarshipCard = document.createElement('div');
            scholarshipCard.classList.add('scholarship-card');
            scholarshipCard.innerHTML = `
                <h3>${scholarship.title}</h3>
                <div class="scholarship-details">
                    <span>Award Amount: ${scholarship.amount}</span>
                    <span>Deadline: ${scholarship.deadline}</span>
                    <span>Grade Level: ${scholarship.grade_level}</span>
                </div>
                <a href="${scholarship.link}" 
                target="_blank" 
                class="btn btn-primary">
                    Apply on External Site
                </a>
            `;
            scholarshipsList.appendChild(scholarshipCard);
        });
    }

    // Setup pagination controls
    function setupPagination() {
        const prevButton = document.getElementById('prev-page');
        const nextButton = document.getElementById('next-page');
        const currentPageSpan = document.getElementById('current-page');

        const totalPages = Math.ceil(filteredScholarships.length / ITEMS_PER_PAGE);

        // Update page number display
        currentPageSpan.textContent = `Page ${currentPage} of ${totalPages}`;

        // Enable/disable previous button
        prevButton.disabled = currentPage === 1;

        // Enable/disable next button
        nextButton.disabled = currentPage === totalPages;

        // Previous page handler
        prevButton.onclick = () => {
            if (currentPage > 1) {
                currentPage--;
                renderScholarships();
                setupPagination();
            }
        };

        // Next page handler
        nextButton.onclick = () => {
            if (currentPage < totalPages) {
                currentPage++;
                renderScholarships();
                setupPagination();
            }
        };
    }

    // Event listeners
    document.addEventListener('DOMContentLoaded', () => {
        loadScholarships();
        
        document.getElementById('search').addEventListener('input', filterScholarships);
        document.getElementById('amount').addEventListener('change', filterScholarships);
    });
    </script>
</body>
</html>