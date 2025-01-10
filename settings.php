<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fbla";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle logout
if (isset($_POST['logout'])) {
    $_SESSION = array();
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-3600, '/');
    }
    session_destroy();
    header("Location: loginpage.php");
    exit();
}

$isLoggedIn = isset($_SESSION['user_id']);
$userType = $_SESSION['user_type'] ?? null;
$userId = $_SESSION['user_id'] ?? null;

if (!$isLoggedIn) {
    header("Location: loginpage.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $table = ($userType === 'student') ? 'students' : 'employers';
    $updates = [];
    $types = "";
    $params = [];

    if ($userType === 'student') {
        if (isset($_POST['bio'])) {
            $updates[] = "bio = ?";
            $types .= "s";
            $params[] = $_POST['bio'];
        }
        if (isset($_POST['skills'])) {
            $updates[] = "skills = ?";
            $types .= "s";
            $params[] = $_POST['skills'];
        }
        if (isset($_POST['education'])) {
            $updates[] = "education = ?";
            $types .= "s";
            $params[] = $_POST['education'];
        }
    } else {
        if (isset($_POST['company_description'])) {
            $updates[] = "company_description = ?";
            $types .= "s";
            $params[] = $_POST['company_description'];
        }
        if (isset($_POST['company_website'])) {
            $updates[] = "company_website = ?";
            $types .= "s";
            $params[] = $_POST['company_website'];
        }
        if (isset($_POST['company_location'])) {
            $updates[] = "company_location = ?";
            $types .= "s";
            $params[] = $_POST['company_location'];
        }
        if (isset($_POST['industry'])) {
            $updates[] = "industry = ?";
            $types .= "s";
            $params[] = $_POST['industry'];
        }
    }

    if (!empty($updates)) {
        $params[] = $userId;
        $types .= "i";
        
        $sql = "UPDATE $table SET " . implode(", ", $updates) . " WHERE id = ?";
        $stmt = $conn->prepare($sql);
        
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            $success_message = "Profile updated successfully!";
        } else {
            $error_message = "Error updating profile: " . $conn->error;
        }
        $stmt->close();
    }
}

// Fetch current user data
$table = ($userType === 'student') ? 'students' : 'employers';
$sql = "SELECT * FROM $table WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link rel="stylesheet" href="navbar-responsive.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        
        .settings-container {
            max-width: 800px;
            margin: 100px auto;
            padding: 20px;
        }

        .settings-section {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .settings-section h2 {
            margin-bottom: 20px;
            color: #f57f17;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }

        .form-group input[type="text"],
        .form-group input[type="url"],
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            background-color: white;
        }

        .form-group textarea {
            height: 120px;
            resize: vertical;
        }

        .form-group input[type="file"] {
            display: block;
            margin-top: 8px;
        }

        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 20px;
            border: 3px solid #f57f17;
        }

        .file-info {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .submit-button {
            background-color: #f57f17;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .submit-button:hover {
            background-color: #e65100;
        }

        .logout-button {
            background-color: #dc3545;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
            transition: background-color 0.3s;
        }

        .logout-button:hover {
            background-color: #c82333;
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
                    <a href="employerdash.php">Employer Dashboard</a>
                <?php endif; ?>
                <a href="settings.php" class="profile-link">
                    <img src="./media/socialimage2.jpg" alt="Profile" class="profile-pic" />
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="settings-container">
        <h1>Profile Settings</h1>
        
        <?php if (isset($success_message)): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="settings-section">
            <form method="POST" action="" enctype="multipart/form-data">
                <!-- Profile Picture UI (not connected) -->
                <div class="form-group">
                    <label>Profile Picture</label>
                    <img src="./media/socialimage2.jpg" alt="Profile Picture" class="profile-picture">
                    <input type="file" name="profile_picture" accept="image/*">
                    <div class="file-info">Accepted formats: JPG, JPEG, PNG, GIF</div>
                </div>

                <?php if ($userType === 'student'): ?>
                    <!-- Student Fields -->
                    <div class="form-group">
                        <label for="bio">Bio</label>
                        <textarea name="bio" id="bio" placeholder="Tell us about yourself"><?php echo htmlspecialchars($userData['bio'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="skills">Skills</label>
                        <textarea name="skills" id="skills" placeholder="List your key skills"><?php echo htmlspecialchars($userData['skills'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="education">Education</label>
                        <textarea name="education" id="education" placeholder="Your educational background"><?php echo htmlspecialchars($userData['education'] ?? ''); ?></textarea>
                    </div>

                <?php else: ?>
                    <!-- Employer Fields -->
                    <div class="form-group">
                        <label for="company_description">Company Description</label>
                        <textarea name="company_description" id="company_description" placeholder="Describe your company"><?php echo htmlspecialchars($userData['company_description'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="company_website">Company Website</label>
                        <input type="url" name="company_website" id="company_website" placeholder="https://example.com" value="<?php echo htmlspecialchars($userData['company_website'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="company_location">Company Location</label>
                        <input type="text" name="company_location" id="company_location" placeholder="City, State" value="<?php echo htmlspecialchars($userData['company_location'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="industry">Industry</label>
                        <input type="text" name="industry" id="industry" placeholder="e.g. Technology, Healthcare, Finance" value="<?php echo htmlspecialchars($userData['industry'] ?? ''); ?>">
                    </div>
                <?php endif; ?>

                <button type="submit" name="update_profile" class="submit-button">Save Changes</button>
            </form>

            <form method="POST" action="">
                <button type="submit" name="logout" class="logout-button">Log Out</button>
            </form>
        </div>
    </div>
</body>
</html>