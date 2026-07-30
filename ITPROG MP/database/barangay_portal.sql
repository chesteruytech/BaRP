
DROP DATABASE IF EXISTS barangay_portal;
CREATE DATABASE barangay_portal;
USE barangay_portal;

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('resident','admin') NOT NULL,
    status ENUM('Active','Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


CREATE TABLE households (
    household_id INT AUTO_INCREMENT PRIMARY KEY,
    household_number VARCHAR(30) NOT NULL,
    address VARCHAR(255) NOT NULL,
    income_level ENUM('Low','Middle','High') DEFAULT 'Middle'
) ENGINE=InnoDB;



CREATE TABLE residents (
    resident_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    household_id INT NOT NULL,

    first_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50),
    last_name VARCHAR(50) NOT NULL,
    suffix VARCHAR(20),

    birthdate DATE NOT NULL,

    gender ENUM('Male','Female') NOT NULL,

    civil_status ENUM(
        'Single',
        'Married',
        'Widowed',
        'Separated'
    ) DEFAULT 'Single',

    contact_number VARCHAR(15),

    sector ENUM(
        'General',
        'Senior Citizen',
        'PWD',
        'Student',
        'Solo Parent',
        'Indigent'
    ) DEFAULT 'General',

    verified BOOLEAN DEFAULT FALSE,

    FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    FOREIGN KEY (household_id)
        REFERENCES households(household_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB;



CREATE TABLE documents (

    document_id INT AUTO_INCREMENT PRIMARY KEY,

    resident_id INT NOT NULL,

    document_name VARCHAR(100) NOT NULL,

    file_type VARCHAR(20),

    file_path VARCHAR(255) NOT NULL,

    upload_date DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (resident_id)
        REFERENCES residents(resident_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB;


CREATE TABLE announcements (

    announcement_id INT AUTO_INCREMENT PRIMARY KEY,

    title VARCHAR(150) NOT NULL,

    description TEXT NOT NULL,

    category ENUM(
        'General',
        'Senior',
        'Student',
        'PWD',
        'Solo Parent',
        'Indigent'
    ) DEFAULT 'General',

    event_date DATE,

    admin_id INT NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (admin_id)
        REFERENCES users(user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB;


CREATE TABLE benefits (

    benefit_id INT AUTO_INCREMENT PRIMARY KEY,

    benefit_name VARCHAR(150) NOT NULL,

    description TEXT NOT NULL,

    category ENUM(
        'Senior',
        'Student',
        'Indigent',
        'Disaster'
    ) NOT NULL,

    active BOOLEAN DEFAULT TRUE

) ENGINE=InnoDB;


CREATE TABLE eligibility_rules (

    rule_id INT AUTO_INCREMENT PRIMARY KEY,

    benefit_id INT NOT NULL,

    minimum_age INT,

    maximum_age INT,

    required_sector VARCHAR(50),

    required_income ENUM(
        'Low',
        'Middle',
        'High'
    ),

    household_limit BOOLEAN DEFAULT FALSE,

    FOREIGN KEY (benefit_id)
        REFERENCES benefits(benefit_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB;


CREATE TABLE benefit_requirements (

    requirement_id INT AUTO_INCREMENT PRIMARY KEY,

    benefit_id INT NOT NULL,

    requirement_name VARCHAR(100) NOT NULL,

    FOREIGN KEY (benefit_id)
        REFERENCES benefits(benefit_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB;

CREATE TABLE applications (

    application_id INT AUTO_INCREMENT PRIMARY KEY,

    resident_id INT NOT NULL,

    benefit_id INT NOT NULL,

    application_date DATETIME DEFAULT CURRENT_TIMESTAMP,

    status ENUM(
        'Pending',
        'Approved',
        'Rejected'
    ) DEFAULT 'Pending',

    remarks TEXT,

    updated_at DATETIME,

    FOREIGN KEY (resident_id)
        REFERENCES residents(resident_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    FOREIGN KEY (benefit_id)
        REFERENCES benefits(benefit_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB;

CREATE TABLE application_documents (

    application_document_id INT AUTO_INCREMENT PRIMARY KEY,

    application_id INT NOT NULL,

    requirement_id INT NOT NULL,

    file_path VARCHAR(255) NOT NULL,

    FOREIGN KEY (application_id)
        REFERENCES applications(application_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    FOREIGN KEY (requirement_id)
        REFERENCES benefit_requirements(requirement_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB;

CREATE TABLE certificate_types (

    certificate_type_id INT AUTO_INCREMENT PRIMARY KEY,

    certificate_name VARCHAR(100) NOT NULL UNIQUE

) ENGINE=InnoDB;

CREATE TABLE certificate_requests (

    request_id INT AUTO_INCREMENT PRIMARY KEY,

    resident_id INT NOT NULL,

    certificate_type_id INT NOT NULL,

    request_date DATETIME DEFAULT CURRENT_TIMESTAMP,

    status ENUM(
        'Pending',
        'Approved',
        'Rejected'
    ) DEFAULT 'Pending',

    remarks TEXT,

    updated_at DATETIME,

    FOREIGN KEY (resident_id)
        REFERENCES residents(resident_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    FOREIGN KEY (certificate_type_id)
        REFERENCES certificate_types(certificate_type_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB;


