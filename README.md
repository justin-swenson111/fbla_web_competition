# FBLA Job Board Platform

## 🎯 Project Overview
A comprehensive job board platform designed for FBLA (Future Business Leaders of America) to connect students with potential employers. The platform facilitates career exploration and professional networking within the FBLA community.

## ✨ Features

### 🔐 User Authentication & Profiles
```php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$userType = $_SESSION['user_type'] ?? null;
```
- Secure login system with session management
- Dual user types: Students and Employers
- Customizable profile pages
- Profile picture upload functionality
- Secure password management with hashing

### 👨‍🎓 Student Features
```php
if ($userType === 'student') {
    // Student-specific features
    $fields = ['bio', 'skills', 'education'];
}
```
- Personalized student profiles with:
  - Bio section
  - Skills listing
  - Educational background
  - Profile picture customization
- Ability to view and apply to job postings

### 💼 Employer Features
```php
if ($userType === 'employer') {
    // Employer-specific features
    $fields = ['company_description', 'website', 'location', 'industry'];
}
```
- Comprehensive company profiles including:
  - Company description
  - Website URL
  - Location information
  - Industry specification
  - Company logo/profile picture
- Job posting capabilities

## 🛠 Technical Stack

### Backend
```php
// Database connection
$conn = new mysqli($servername, $username, $password, $dbname);
```
- PHP 7.4+
- MySQL Database
- Session-based authentication

### Frontend
```html
<div class="settings-container">
    <!-- Your responsive interface here -->
</div>
```
- HTML5
- CSS3
- JavaScript
- Responsive design

## 📦 Installation

### 1. Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Web browser with JavaScript enabled

### 2. Database Setup
```sql
CREATE DATABASE fbla;
USE fbla;

-- Create necessary tables
CREATE TABLE students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    -- Add student fields
);

CREATE TABLE employers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    -- Add employer fields
);
```

### 3. Configuration
```php
// Update in your connection file
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fbla";
```

### 4. File Structure
```
/
├── index.php               # Main entry point
├── loginpage.php          # Authentication page
├── uploads/               # User uploads
│   └── pfps/             # Profile pictures
├── media/                # Static media
│   └── default-image.png
├── css/                  # Stylesheets
├── js/                   # JavaScript files
└── includes/             # PHP includes
```

### 5. Directory Permissions
```bash
# Make uploads directory writable
chmod 777 uploads/pfps
```

## 🔒 Security Features

### Password Hashing
```php
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
```

### SQL Injection Prevention
```php
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
```

### XSS Prevention
```php
echo htmlspecialchars($userData['bio'] ?? '');
```

## 📝 Usage Guidelines

### For Students
1. Register account:
```php
// Registration process
if ($userType === 'student') {
    // Create student profile
}
```

### For Employers
1. Create company account:
```php
// Registration process
if ($userType === 'employer') {
    // Create employer profile
}
```

## 📤 File Upload Specifications

### Profile Pictures
```php
// Allowed formats
$allowed_types = array('jpg', 'jpeg', 'png', 'gif');
$target_dir = "uploads/pfps/";
```

## ⚠️ Error Handling
```php
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
```

## 🔄 Maintenance

### Database Optimization
```sql
-- Regular cleanup
DELETE FROM unused_files WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
```

*Last updated: January 15, 2025*