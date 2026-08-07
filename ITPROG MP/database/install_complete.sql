
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


USE barangay_portal;

-- Supplemental schema required by the written specification. The original schema file is left untouched.
CREATE TABLE IF NOT EXISTS resident_registry (
    registry_id INT AUTO_INCREMENT PRIMARY KEY,
    household_number VARCHAR(30) NOT NULL,
    address VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    birthdate DATE NOT NULL,
    sector ENUM('General','Senior Citizen','PWD','Student','Solo Parent','Indigent') DEFAULT 'General',
    income_level ENUM('Low','Middle','High') DEFAULT 'Middle',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_registry_identity (first_name,last_name,birthdate,address)
) ENGINE=InnoDB;

ALTER TABLE residents
    ADD COLUMN IF NOT EXISTS registry_id INT NULL,
    ADD COLUMN IF NOT EXISTS disaster_affected BOOLEAN DEFAULT FALSE;

ALTER TABLE applications
    ADD COLUMN IF NOT EXISTS submitted_by_resident_id INT NULL;

ALTER TABLE eligibility_rules
    ADD COLUMN IF NOT EXISTS required_location VARCHAR(100) NULL,
    ADD COLUMN IF NOT EXISTS requires_disaster_affected BOOLEAN DEFAULT FALSE;

-- Default administrator. Change this password immediately after first login.
-- Email: admin@barangay.local   Password: Admin123!
INSERT INTO users (email,password,role,status)
SELECT 'admin@barangay.local', '$2y$12$LrakB6AtTrpaHJvclF2hFeLP0IlvBEP26APT0fWxMU9G8Dsve52Qq', 'admin', 'Active'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email='admin@barangay.local');

INSERT INTO certificate_types (certificate_name)
SELECT 'Barangay Clearance' WHERE NOT EXISTS (SELECT 1 FROM certificate_types WHERE certificate_name='Barangay Clearance');
INSERT INTO certificate_types (certificate_name)
SELECT 'Certificate of Residency' WHERE NOT EXISTS (SELECT 1 FROM certificate_types WHERE certificate_name='Certificate of Residency');
INSERT INTO certificate_types (certificate_name)
SELECT 'Business Clearance Endorsement' WHERE NOT EXISTS (SELECT 1 FROM certificate_types WHERE certificate_name='Business Clearance Endorsement');

INSERT INTO benefits (benefit_name,description,category,active)
SELECT 'Senior Citizen Assistance', 'Allowance, birthday cash gift, or medicine assistance for verified residents aged 60 and above. Each qualified senior citizen may apply individually.', 'Senior', 1
WHERE NOT EXISTS (SELECT 1 FROM benefits WHERE benefit_name='Senior Citizen Assistance');
INSERT INTO benefits (benefit_name,description,category,active)
SELECT 'Indigent / Low-Income Assistance', 'Food packs or emergency allowance for verified low-income residents. Limited to one active or approved claim per household.', 'Indigent', 1
WHERE NOT EXISTS (SELECT 1 FROM benefits WHERE benefit_name='Indigent / Low-Income Assistance');
INSERT INTO benefits (benefit_name,description,category,active)
SELECT 'Student Assistance', 'School supplies or cash aid for verified student residents. Parents or guardians may submit on behalf of a registered student in the same household.', 'Student', 1
WHERE NOT EXISTS (SELECT 1 FROM benefits WHERE benefit_name='Student Assistance');
INSERT INTO benefits (benefit_name,description,category,active)
SELECT 'Disaster or Emergency Assistance', 'Relief goods or shelter support for verified residents marked as affected by a current calamity or emergency. Limited to one claim per household.', 'Disaster', 1
WHERE NOT EXISTS (SELECT 1 FROM benefits WHERE benefit_name='Disaster or Emergency Assistance');

INSERT INTO eligibility_rules (benefit_id,minimum_age,required_sector,household_limit)
SELECT b.benefit_id,60,'Senior Citizen',0 FROM benefits b WHERE b.benefit_name='Senior Citizen Assistance'
AND NOT EXISTS (SELECT 1 FROM eligibility_rules e WHERE e.benefit_id=b.benefit_id);
INSERT INTO eligibility_rules (benefit_id,required_income,household_limit)
SELECT b.benefit_id,'Low',1 FROM benefits b WHERE b.benefit_name='Indigent / Low-Income Assistance'
AND NOT EXISTS (SELECT 1 FROM eligibility_rules e WHERE e.benefit_id=b.benefit_id);
INSERT INTO eligibility_rules (benefit_id,required_sector,household_limit)
SELECT b.benefit_id,'Student',0 FROM benefits b WHERE b.benefit_name='Student Assistance'
AND NOT EXISTS (SELECT 1 FROM eligibility_rules e WHERE e.benefit_id=b.benefit_id);
INSERT INTO eligibility_rules (benefit_id,household_limit,requires_disaster_affected)
SELECT b.benefit_id,1,1 FROM benefits b WHERE b.benefit_name='Disaster or Emergency Assistance'
AND NOT EXISTS (SELECT 1 FROM eligibility_rules e WHERE e.benefit_id=b.benefit_id);

INSERT INTO benefit_requirements (benefit_id,requirement_name)
SELECT b.benefit_id,'Valid Barangay or Government ID' FROM benefits b WHERE b.benefit_name='Senior Citizen Assistance'
AND NOT EXISTS (SELECT 1 FROM benefit_requirements r WHERE r.benefit_id=b.benefit_id AND r.requirement_name='Valid Barangay or Government ID');
INSERT INTO benefit_requirements (benefit_id,requirement_name)
SELECT b.benefit_id,'Senior Citizen ID' FROM benefits b WHERE b.benefit_name='Senior Citizen Assistance'
AND NOT EXISTS (SELECT 1 FROM benefit_requirements r WHERE r.benefit_id=b.benefit_id AND r.requirement_name='Senior Citizen ID');
INSERT INTO benefit_requirements (benefit_id,requirement_name)
SELECT b.benefit_id,'Valid Barangay or Government ID' FROM benefits b WHERE b.benefit_name='Indigent / Low-Income Assistance'
AND NOT EXISTS (SELECT 1 FROM benefit_requirements r WHERE r.benefit_id=b.benefit_id AND r.requirement_name='Valid Barangay or Government ID');
INSERT INTO benefit_requirements (benefit_id,requirement_name)
SELECT b.benefit_id,'Proof of Low Income or Indigency' FROM benefits b WHERE b.benefit_name='Indigent / Low-Income Assistance'
AND NOT EXISTS (SELECT 1 FROM benefit_requirements r WHERE r.benefit_id=b.benefit_id AND r.requirement_name='Proof of Low Income or Indigency');
INSERT INTO benefit_requirements (benefit_id,requirement_name)
SELECT b.benefit_id,'School ID or Proof of Enrollment' FROM benefits b WHERE b.benefit_name='Student Assistance'
AND NOT EXISTS (SELECT 1 FROM benefit_requirements r WHERE r.benefit_id=b.benefit_id AND r.requirement_name='School ID or Proof of Enrollment');
INSERT INTO benefit_requirements (benefit_id,requirement_name)
SELECT b.benefit_id,'Valid Barangay or Government ID' FROM benefits b WHERE b.benefit_name='Disaster or Emergency Assistance'
AND NOT EXISTS (SELECT 1 FROM benefit_requirements r WHERE r.benefit_id=b.benefit_id AND r.requirement_name='Valid Barangay or Government ID');
