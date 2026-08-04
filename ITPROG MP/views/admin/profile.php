<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
requireAdmin();

$db = (new Database())->connect();
$userId = $_SESSION['user_id'];

$message = '';

// Update email
if (isset($_POST['update_email'])) {
    $stmt = $db->prepare("UPDATE users SET email = ? WHERE user_id = ?");
    $stmt->execute([trim($_POST['email']), $userId]);
    $_SESSION['email'] = trim($_POST['email']);
    $message = 'Email updated successfully.';
}

// Update password
if (isset($_POST['update_password'])) {
    if ($_POST['new_password'] === $_POST['confirm_password'] && $_POST['new_password'] !== '') {
        $hashed = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->execute([$hashed, $userId]);
        $message = 'Password changed successfully.';
    } else {
        $message = 'Passwords do not match.';
    }
}

$stmt = $db->prepare("SELECT email, role, created_at FROM users WHERE user_id = ?");
$stmt->execute([$userId]);
$admin = $stmt->fetch();

$pageTitle  = 'My Profile';
$activePage = 'profile';
include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/sidebar.php';
?>

<h2><i class="bi bi-person-gear"></i> My Profile</h2>

<?php if ($message): ?>
    <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Account Information</div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control"
                               value="<?= htmlspecialchars($admin['email']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars(ucfirst($admin['role'])) ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Member Since</label>
                        <input type="text" class="form-control" value="<?= date('M d, Y', strtotime($admin['created_at'])) ?>" disabled>
                    </div>
                    <button type="submit" name="update_email" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Change Password</div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" name="update_password" class="btn btn-primary">
                        <i class="bi bi-shield-lock"></i> Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
