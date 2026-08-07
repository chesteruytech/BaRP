<?php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../models/Benefit.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'apply') {
    requireResident();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
        header('Location: ../views/resident/programs.php'); 
        exit; 
    }

    $db = portalDb();
    $submitter = currentResident($db);
    $benefitId = (int)($_POST['benefit_id'] ?? 0);
    $benefit = Benefit::find($db,$benefitId);
    if (!$submitter || !$benefit || empty($benefit['active'])) 
        redirectWithMessage('../views/resident/programs.php','error','Program is unavailable.');

    $beneficiaryId = (int)($_POST['beneficiary_resident_id'] ?? $submitter['resident_id']);
    $stmt = $db->prepare("SELECT r.*,h.household_number,h.address,h.income_level 
                          FROM residents r 
                          JOIN households h ON h.household_id=r.household_id 
                          WHERE r.resident_id=? 
                          AND r.household_id=?");

    $stmt->execute([$beneficiaryId,$submitter['household_id']]);
    $beneficiary = $stmt->fetch();

    if (!$beneficiary) 
        redirectWithMessage('../views/resident/programs.php','error','Beneficiary must belong to your household.');
    if ($benefit['category'] !== 'Student' && $beneficiaryId !== (int)$submitter['resident_id']) 
        redirectWithMessage('../views/resident/programs.php','error','Only Student Assistance may be submitted 
                            on behalf of another household member.');

    $eligibility = checkBenefitEligibility($db,$beneficiary,$benefit);
    if (!$eligibility['eligible']) 
        redirectWithMessage('../views/resident/programs.php','error',implode(' ', $eligibility['reasons']));

    $requirements = benefitRequirements($db,$benefitId);
    $selected = $_POST['requirement_document'] ?? [];
    foreach ($requirements as $req) {
        $docId = (int)($selected[$req['requirement_id']] ?? 0);
        if (!$docId) redirectWithMessage('../views/resident/programs.php','error','Please attach a stored document for every program requirement.');
        $stmt = $db->prepare('SELECT document_id 
                              FROM documents 
                              WHERE document_id=? 
                              AND resident_id=?');
        $stmt->execute([$docId,$beneficiaryId]);
        if (!$stmt->fetch()) 
            redirectWithMessage('../views/resident/programs.php','error','One or more selected documents are invalid for the beneficiary.');
    }

    try {
        $db->beginTransaction();
        $stmt = $db->prepare('INSERT INTO applications(resident_id,benefit_id,submitted_by_resident_id) 
                              VALUES(?,?,?)');
        $stmt->execute([$beneficiaryId,$benefitId,$submitter['resident_id']]);
        $appId = (int)$db->lastInsertId();

        foreach ($requirements as $req) {
            $docId = (int)$selected[$req['requirement_id']];
            $stmt = $db->prepare('SELECT file_path 
                                  FROM documents 
                                  WHERE document_id=?');
            $stmt->execute([$docId]);
            $filePath = $stmt->fetchColumn();
            $db->prepare('INSERT INTO application_documents(application_id,requirement_id,file_path) 
                          VALUES(?,?,?)')->execute([$appId,$req['requirement_id'],$filePath]);
        }

        $db->commit();

    } catch (Throwable $e) {
        if ($db->inTransaction()) $db->rollBack();
        redirectWithMessage('../views/resident/programs.php','error','Application could not be submitted.');
    }
    redirectWithMessage('../views/resident/applications.php','success','Application submitted successfully.');
}

requireAdmin();
$db = portalDb();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
    header('Location: ../views/admin/programs.php'); 
    exit; 
}

if ($action === 'save_program') {
    $id = (int)($_POST['benefit_id'] ?? 0);
    $name = trim($_POST['benefit_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = $_POST['category'] ?? '';
    $active = isset($_POST['active']) ? 1 : 0;

    $min = $_POST['minimum_age'] !== '' ? (int)$_POST['minimum_age'] : null;
    $max = $_POST['maximum_age'] !== '' ? (int)$_POST['maximum_age'] : null;
    $sector = trim($_POST['required_sector'] ?? '') ?: null;
    $income = trim($_POST['required_income'] ?? '') ?: null;
    $location = trim($_POST['required_location'] ?? '') ?: null;
    $householdLimit = isset($_POST['household_limit']) ? 1 : 0;
    $disaster = isset($_POST['requires_disaster_affected']) ? 1 : 0;

    if ($name === '' || $description === '' || !in_array($category,['Senior','Student','Indigent','Disaster'],true)) 
        redirectWithMessage('../views/admin/programs.php','error','Complete the required program fields.');

    $db->beginTransaction();
    
    if ($id) {
        $db->prepare('UPDATE benefits 
                      SET benefit_name=?,description=?,category=?,active=? 
                      WHERE benefit_id=?')->execute([$name,$description,$category,$active,$id]);
    } else {
        $db->prepare('INSERT INTO benefits(benefit_name,description,category,active) 
                      VALUES(?,?,?,?)')->execute([$name,$description,$category,$active]);
        $id = (int)$db->lastInsertId();
    }

    $stmt = $db->prepare('SELECT rule_id FROM eligibility_rules WHERE benefit_id=? LIMIT 1');
    $stmt->execute([$id]);
    $ruleId = $stmt->fetchColumn();

    if ($ruleId) {
        $db->prepare('UPDATE eligibility_rules 
                      SET minimum_age=?,maximum_age=?,required_sector=?,required_income=?,household_limit=?,required_location=?,requires_disaster_affected=? 
                      WHERE rule_id=?')
           ->execute([$min,$max,$sector,$income,$householdLimit,$location,$disaster,$ruleId]);
    } else {
        $db->prepare('INSERT INTO eligibility_rules(benefit_id,minimum_age,maximum_age,required_sector,required_income,household_limit,required_location,requires_disaster_affected) 
                      VALUES(?,?,?,?,?,?,?,?)')
           ->execute([$id,$min,$max,$sector,$income,$householdLimit,$location,$disaster]);
    }

    $db->commit();
    redirectWithMessage('../views/admin/programs.php','success','Program and eligibility rules saved.');
}

if ($action === 'delete_program') {
    $id=(int)($_POST['benefit_id']??0);

    try { 
        $db->prepare('DELETE FROM benefits 
                        WHERE benefit_id=?')->execute([$id]); 
            redirectWithMessage('../views/admin/programs.php','success','Program deleted.'); 
    }
    
    catch (Throwable $e) { 
        redirectWithMessage('../views/admin/programs.php','error','Program cannot be deleted while applications reference it. Deactivate it instead.'); 
    }
}

if ($action === 'add_requirement') {
    $id=(int)($_POST['benefit_id']??0); 
    $name=trim($_POST['requirement_name']??'');

    if ($id && $name!=='') 
        $db->prepare('INSERT INTO benefit_requirements(benefit_id,requirement_name) 
                      VALUES(?,?)')->execute([$id,$name]);

    redirectWithMessage('../views/admin/requirements.php?benefit_id='.$id,'success','Requirement added.');
}

if ($action === 'delete_requirement') {
    $req=(int)($_POST['requirement_id']??0); 
    $benefit=(int)($_POST['benefit_id']??0);

    try { 
        $db->prepare('DELETE FROM benefit_requirements 
                      WHERE requirement_id=?')->execute([$req]); 
        redirectWithMessage('../views/admin/requirements.php?benefit_id='.$benefit,'success','Requirement removed.'); 
    }

    catch (Throwable $e) { 
        redirectWithMessage('../views/admin/requirements.php?benefit_id='.$benefit,'error','Requirement is already referenced by an application and cannot be removed.'); 
    }
}

header('Location: ../views/admin/programs.php');
