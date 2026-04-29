<?php

$pageTitle = 'My Profile';
$pageCSS = 'auth.css';
require_once 'includes/db-connection.php';
require_once 'includes/auth-check.php';
require_once 'includes/logger.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
requireLogin();

$error = '';
$success = '';
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $newPassword = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirmNewPassword = isset($_POST['confirm_new_password']) ? $_POST['confirm_new_password'] : '';
    if (empty($username) || strlen($username) < 3 || strlen($username) > 50) {
        $error = 'Username must be between 3 and 50 characters.';
    } 
    elseif (strlen($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address (max 100 characters).';
    } 
    else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
        $stmt->execute([$username, $email, $_SESSION['user_id']]);
        
        if ($stmt->fetch()) {
            $error = 'Username or email already taken by another user.';
        } else {
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            $stmt->execute([$username, $email, $_SESSION['user_id']]);
            $_SESSION['username'] = $username;
            if (!empty($newPassword)) {
                if (strlen($newPassword) < 6 || strlen($newPassword) > 255) {
                    $error = 'Password must be between 6 and 255 characters.';
                } elseif (!preg_match('/[a-z]/', $newPassword)) {
                    $error = 'Password must contain at least one lowercase letter.';
                } elseif (!preg_match('/[A-Z]/', $newPassword)) {
                    $error = 'Password must contain at least one uppercase letter.';
                } elseif (!preg_match('/[0-9]/', $newPassword)) {
                    $error = 'Password must contain at least one number.';
                } elseif ($newPassword !== $confirmNewPassword) {
                    $error = 'New passwords do not match.';
                } else {
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$hashedPassword, $_SESSION['user_id']]);
                    logAction($pdo, $_SESSION['user_id'], 'PASSWORD_CHANGED', 'User changed their password');
                }
            }
            if (empty($error)) {
                logAction($pdo, $_SESSION['user_id'], 'PROFILE_UPDATED', 'User updated profile');
                $success = 'Profile updated successfully!';
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch();
            }
        }
    }
}

require_once 'includes/header.inc.php';
?>

<div class="profile-page">
    <div class="form-container" style="max-width:600px;">
        <h2>👤 My Profile</h2>
        <?php if (!empty($error)): ?>
            <div class="server-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div style="background:rgba(0,184,148,.1);color:#00b894;padding:10px 14px;border-radius:8px;margin-bottom:16px;">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        <form id="profileForm" method="POST" action="profile.php" novalidate>
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" maxlength="50" minlength="3"
                       value="<?php echo htmlspecialchars($user['username']); ?>">
                <span class="form-error">Error</span>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" maxlength="100"
                       value="<?php echo htmlspecialchars($user['email']); ?>">
                <span class="form-error">Error</span>
            </div>
            <hr style="margin:24px 0;border:none;border-top:2px solid #dfe6e9;">
            <h3 style="margin-bottom:16px;color:#636e72;font-size:.95rem;">Change Password (optional)</h3>

            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" placeholder="Leave blank to keep current" maxlength="255" minlength="6">
                <span class="form-error">Error</span>
            </div>
            
            <div class="form-group">
                <label for="confirm_new_password">Confirm New Password</label>
                <input type="password" id="confirm_new_password" name="confirm_new_password" placeholder="Re-enter new password" maxlength="255" minlength="6">
                <span class="form-error">Error</span>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Save Changes</button>
        </form>
        <div class="form-footer" style="margin-top:24px;">
            <p>Member since: <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
            <p>Role: 
                <span class="status-badge status-<?php echo $user['role'] === 'admin' ? 'shipped' : 'processing'; ?>">
                    <?php echo ucfirst($user['role']); ?>
                </span>
            </p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.inc.php'; ?>
