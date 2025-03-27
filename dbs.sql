CREATE TABLE students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    fname VARCHAR(50) NOT NULL,
    lname VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    pass VARCHAR(255) NOT NULL,
    user_type VARCHAR(20) NOT NULL DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    profile_picture VARCHAR(255),
    bio TEXT,
    skills TEXT,
    education TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL
);

CREATE TABLE employers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    fname VARCHAR(50) NOT NULL,
    lname VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    pass VARCHAR(255) NOT NULL,
    company_name VARCHAR(100) NOT NULL,
    user_type VARCHAR(20) NOT NULL DEFAULT 'employer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    company_description TEXT,
    company_website VARCHAR(255),
    company_location VARCHAR(255),
    industry VARCHAR(100),
    profile_picture VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL
);

CREATE TABLE job_postings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employer_id INT NOT NULL,
    company_name VARCHAR(100) NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    requirements TEXT NOT NULL,
    salary DECIMAL(10,2) NOT NULL,
    location VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    application_deadline DATE,
    job_type VARCHAR(50),
    FOREIGN KEY (employer_id) REFERENCES employers(id),
    approval_status VARCHAR(50) NOT NULL DEFAULT 'Pending',
);

CREATE TABLE job_applications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    job_posting_id INT NOT NULL,
    employer_id INT NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'Pending', -- e.g., Pending, Reviewed, Accepted, Rejected
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    cover_letter TEXT,
    resume_path VARCHAR(255), -- Path to resume if submitted with the application
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (job_posting_id) REFERENCES job_postings(id) ON DELETE CASCADE,
    FOREIGN KEY (employer_id) REFERENCES employers(id) ON DELETE CASCADE
);

CREATE TABLE admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    fname VARCHAR(50) NOT NULL,
    lname VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    pass VARCHAR(255) NOT NULL,
    user_type VARCHAR(20) NOT NULL DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    profile_picture VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL
);

CREATE TABLE employer_waitlist (
    id INT PRIMARY KEY AUTO_INCREMENT,
    fname VARCHAR(50) NOT NULL,
    lname VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    pass VARCHAR(255) NOT NULL,
    company_name VARCHAR(100) NOT NULL,
    user_type VARCHAR(20) NOT NULL DEFAULT 'employer_pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    company_description TEXT,
    company_website VARCHAR(255),
    company_location VARCHAR(255),
    industry VARCHAR(100),
    profile_picture VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    approval_status VARCHAR(50) NOT NULL DEFAULT 'Pending', -- Pending, Approved, Rejected
    last_login TIMESTAMP NULL
);

CREATE TABLE system_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activity_type VARCHAR(50),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE job_posting_waitlist (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employer_id INT NOT NULL,
    company_name VARCHAR(100) NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    requirements TEXT NOT NULL,
    salary DECIMAL(10,2) NOT NULL,
    location VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    application_deadline DATE,
    job_type VARCHAR(50),
    job_subject VARCHAR(100),
    approval_status VARCHAR(50) NOT NULL DEFAULT 'Pending',
    FOREIGN KEY (employer_id) REFERENCES employers(id)
);