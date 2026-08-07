USE barangay_portal;

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

-- Default administrator.
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
