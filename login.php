<?php

$pageTitle = 'Login';
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
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } 
    elseif (strlen($email) > 100) {
        $error = 'Email must be at most 100 characters.';
    } 
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } 
    elseif (strlen($password) > 255) {
        $error = 'Password is too long.';
    } 
    else {
        // Authenticate user credentials against database
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            logAction($pdo, $user['id'], 'LOGIN', 'User logged in successfully');
            $_SESSION['message'] = 'Welcome back, ' . htmlspecialchars($user['username']) . '!';
            $_SESSION['message_type'] = 'success';
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

require_once 'includes/header.inc.php';
?>

<div class="form-page">
    <div class="form-container">
        <h2>🔐 Login</h2>
        <?php if (!empty($error)): ?>
            <div class="server-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form id="loginForm" method="POST" action="login.php" novalidate>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="your@email.com" maxlength="100"
                       value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                <span class="form-error">Error message</span> 
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" maxlength="255" minlength="6">
                <span class="form-error">Error message</span>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>

        <div class="form-footer">
            <p>Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.inc.php'; ?>
