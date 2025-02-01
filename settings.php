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

// Create uploads/pfps directory if it doesn't exist
if (!file_exists('uploads/pfps')) {
    mkdir('uploads/pfps', 0777, true);
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

// Handle profile picture upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['profile_picture'])) {
    $target_dir = "uploads/pfps/";
    $file_extension = strtolower(pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION));
    $new_filename = "pfp_" . $userId . "_" . time() . "." . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    // Check if image file is valid
    $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
    if (in_array($file_extension, $allowed_types)) {
        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            // Update database with new profile picture path
            $table = ($userType === 'student') ? 'students' : 'employers';
            $sql = "UPDATE $table SET profile_picture = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $target_file, $userId);
            
            if ($stmt->execute()) {
                $success_message = "Profile picture updated successfully!";
            } else {
                $error_message = "Error updating profile picture in database.";
            }
            $stmt->close();
        } else {
            $error_message = "Sorry, there was an error uploading your file.";
        }
    } else {
        $error_message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
    }
}

// Handle password change
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // First verify the current password
    $table = ($userType === 'student') ? 'students' : 'employers';
    $sql = "SELECT pass FROM $table WHERE id = ?";  // Changed 'password' to 'pass'
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if (password_verify($current_password, $user['pass'])) {  // Changed 'password' to 'pass'
        if ($new_password === $confirm_password) {
            if (strlen($new_password) >= 8) {  // Minimum password length
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $sql = "UPDATE $table SET pass = ? WHERE id = ?";  // Changed 'password' to 'pass'
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $hashed_password, $userId);
                
                if ($stmt->execute()) {
                    $success_message = "Password updated successfully!";
                } else {
                    $error_message = "Error updating password.";
                }
                $stmt->close();
            } else {
                $error_message = "New password must be at least 8 characters long.";
            }
        } else {
            $error_message = "New passwords do not match.";
        }
    } else {
        $error_message = "Current password is incorrect.";
    }
}

// Handle form submission for other profile updates
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
    <link rel="shortcut icon" href="./media/favicon.ico" type="image/x-icon" />
    <link rel="stylesheet" href="navbar-responsive.css">
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
            max-width: 100%; /* Ensure form group doesn't exceed container */
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }

        .form-group input[type="text"],
        .form-group input[type="url"],
        .form-group input[type="password"],
        .form-group textarea {
            width: calc(100% - 24px); /* Subtract padding from width */
            max-width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            background-color: white;
            box-sizing: border-box; /* Include padding in width calculation */
        }

        .form-group textarea {
            height: 120px;
            resize: vertical;
        }

        .form-group input[type="file"] {
            display: block;
            margin-top: 8px;
            max-width: 100%;
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

        .password-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }

        .password-requirements {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
            margin-bottom: 15px;
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
                    <a href="./studentdash.php">Student Dashboard</a>
                <?php endif; ?>
                <?php if ($userType === 'employer'): ?>
                    <a href="./employerdash.php">Job Postings</a>
                    <a href="./applicationsrecieved.php">View Applications</a>
                <?php endif; ?>
                <a href="./settings.php" class="profile-link">
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
                <a href="./loginpage.php">Login</a>
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
            <!-- Separate Profile Picture Form -->
            <form method="POST" action="" enctype="multipart/form-data" class="profile-picture-form">
                <div class="form-group">
                    <label>Profile Picture</label>
                    <img src="<?php echo !empty($userData['profile_picture']) ? htmlspecialchars($userData['profile_picture']) : './media/default-image.png'; ?>" alt="Profile Picture" class="profile-picture">
                    <input type="file" name="profile_picture" accept="image/*">
                    <div class="file-info">Accepted formats: JPG, JPEG, PNG, GIF</div>
                    <button type="submit" name="upload_picture" class="submit-button">Update Profile Picture</button>
                </div>
            </form>

            <!-- Separate Profile Information Form -->
            <form method="POST" action="" class="profile-info-form">
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

            <!-- Password Change Form -->
            <form method="POST" action="" class="password-section">
                <h2>Change Password</h2>
                <div class="password-requirements">
                    Password must be at least 8 characters long
                </div>
                
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" name="current_password" id="current_password" required>
                </div>

                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" name="new_password" id="new_password" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" required>
                </div>

                <button type="submit" name="change_password" class="submit-button">Change Password</button>
            </form>

            <form method="POST" action="">
                <button type="submit" name="logout" class="logout-button">Log Out</button>
            </form>
        </div>
    </div>
</body>
</html>