<?php

$pageTitle = 'Register';
$pageCSS = 'auth.css';
require_once 'includes/db-connection.php';
require_once 'includes/logger.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = 'Please fill in all fields.';
    } 
    elseif (strlen($username) < 3 || strlen($username) > 50) {
        $error = 'Username must be between 3 and 50 characters.';
    } 
    elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error = 'Username can only contain letters, numbers, and underscores.';
    } 
    elseif (strlen($email) > 100) {
        $error = 'Email must be at most 100 characters.';
    } 
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } 
    elseif (strlen($password) < 6 || strlen($password) > 255) {
        $error = 'Password must be between 6 and 255 characters.';
    } 
    elseif (!preg_match('/[a-z]/', $password)) {
        $error = 'Password must contain at least one lowercase letter.';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $error = 'Password must contain at least one uppercase letter.';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $error = 'Password must contain at least one number.';
    } 
    elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } 
    else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);

        if ($stmt->fetch()) {
            $error = 'Username or email already exists.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
            $stmt->execute([$username, $email, $hashedPassword]);
            $newUserId = $pdo->lastInsertId();
            logAction($pdo, $newUserId, 'REGISTER', 'New user registered: ' . $username);
            $_SESSION['user_id'] = $newUserId;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = 'user';
            $_SESSION['message'] = 'Account created successfully! Welcome, ' . htmlspecialchars($username) . '!';
            $_SESSION['message_type'] = 'success';
            header('Location: index.php');
            exit;
        }
    }
}

require_once 'includes/header.inc.php';
?>

<div class="form-page">
    <div class="form-container">
        <h2>📝 Create Account</h2>
        <?php if (!empty($error)): ?>
            <div class="server-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form id="registerForm" method="POST" action="register.php" novalidate>
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Choose a username" maxlength="50" minlength="3"
                       value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>">
                <span class="form-error">Error message</span> 
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="your@email.com" maxlength="100"
                       value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                <span class="form-error">Error message</span>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Min. 6 characters" maxlength="255" minlength="6">
                <span class="form-error">Error message</span>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter password" maxlength="255" minlength="6">
                <span class="form-error">Error message</span>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Create Account</button>
        </form>

        <div class="form-footer">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.inc.php'; ?>
