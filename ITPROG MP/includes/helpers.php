<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

function portalDb(): PDO
{
    static $db = null;
    if ($db === null) {
        $db = (new Database())->connect();
    }
    return $db;
}

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirectWithMessage(string $url, string $key, string $message): void
{
    $separator = str_contains($url, '?') ? '&' : '?';
    header('Location: ' . $url . $separator . rawurlencode($key) . '=' . rawurlencode($message));
    exit;
}

function currentResident(PDO $db = null): ?array
{
    $db = $db ?: portalDb();
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    $stmt = $db->prepare("SELECT r.*, h.household_number, h.address, h.income_level, u.email, u.status AS account_status
                          FROM residents r
                          JOIN households h ON h.household_id = r.household_id
                          JOIN users u ON u.user_id = r.user_id
                          WHERE r.user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function residentAge(?string $birthdate): ?int
{
    if (!$birthdate) return null;
    try {
        return (new DateTime($birthdate))->diff(new DateTime('today'))->y;
    } catch (Exception $e) {
        return null;
    }
}

function benefitRule(PDO $db, int $benefitId): ?array
{
    $stmt = $db->prepare("SELECT * FROM eligibility_rules WHERE benefit_id = ? ORDER BY rule_id ASC LIMIT 1");
    $stmt->execute([$benefitId]);
    $rule = $stmt->fetch();
    return $rule ?: null;
}

function benefitRequirements(PDO $db, int $benefitId): array
{
    $stmt = $db->prepare("SELECT * FROM benefit_requirements WHERE benefit_id = ? ORDER BY requirement_id ASC");
    $stmt->execute([$benefitId]);
    return $stmt->fetchAll();
}

function checkBenefitEligibility(PDO $db, array $resident, array $benefit): array
{
    $reasons = [];
    if (empty($resident['verified'])) {
        $reasons[] = 'Resident account is not yet verified.';
    }

    $rule = benefitRule($db, (int)$benefit['benefit_id']);
    if ($rule) {
        $age = residentAge($resident['birthdate'] ?? null);
        if ($rule['minimum_age'] !== null && $age !== null && $age < (int)$rule['minimum_age']) {
            $reasons[] = 'Minimum age is ' . (int)$rule['minimum_age'] . '.';
        }
        if ($rule['maximum_age'] !== null && $age !== null && $age > (int)$rule['maximum_age']) {
            $reasons[] = 'Maximum age is ' . (int)$rule['maximum_age'] . '.';
        }
        if (!empty($rule['required_sector']) && strcasecmp((string)$rule['required_sector'], (string)$resident['sector']) !== 0) {
            $reasons[] = 'Required sector: ' . $rule['required_sector'] . '.';
        }
        if (!empty($rule['required_income']) && strcasecmp((string)$rule['required_income'], (string)$resident['income_level']) !== 0) {
            $reasons[] = 'Required household income level: ' . $rule['required_income'] . '.';
        }
        if (!empty($rule['required_location']) && stripos((string)$resident['address'], (string)$rule['required_location']) === false) {
            $reasons[] = 'Required location: ' . $rule['required_location'] . '.';
        }
        if (!empty($rule['requires_disaster_affected']) && empty($resident['disaster_affected'])) {
            $reasons[] = 'Resident must be marked as affected by the current disaster/emergency.';
        }

        if (!empty($rule['household_limit'])) {
            $stmt = $db->prepare("SELECT COUNT(*)
                                  FROM applications a
                                  JOIN residents r ON r.resident_id = a.resident_id
                                  WHERE r.household_id = ? AND a.benefit_id = ? AND a.status <> 'Rejected'");
            $stmt->execute([$resident['household_id'], $benefit['benefit_id']]);
            if ((int)$stmt->fetchColumn() > 0) {
                $reasons[] = 'This benefit is limited to one active/approved claim per household.';
            }
        } else {
            $stmt = $db->prepare("SELECT COUNT(*) FROM applications
                                  WHERE resident_id = ? AND benefit_id = ? AND status <> 'Rejected'");
            $stmt->execute([$resident['resident_id'], $benefit['benefit_id']]);
            if ((int)$stmt->fetchColumn() > 0) {
                $reasons[] = 'You already have an active or approved application for this benefit.';
            }
        }
    }

    return ['eligible' => empty($reasons), 'reasons' => $reasons, 'rule' => $rule];
}

function allowedUploadExtension(string $name): string
{
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    return in_array($ext, ['pdf','jpg','jpeg','png'], true) ? $ext : '';
}

function ensureUploadDirectory(): string
{
    $dir = __DIR__ . '/../uploads/documents';
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    return $dir;
}

function portalFlashFromQuery(): string
{
    foreach (['success' => 'success', 'error' => 'danger', 'message' => 'info'] as $key => $type) {
        if (!empty($_GET[$key])) {
            return '<div class="alert alert-' . $type . '">' . e($_GET[$key]) . '</div>';
        }
    }
    return '';
}
