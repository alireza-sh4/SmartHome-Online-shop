<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SmartHome Shop - Your one-stop shop for smart home devices">
    <title>SmartHome Shop<?php echo isset($pageTitle) ? ' - ' . htmlspecialchars($pageTitle) : ''; ?></title>
    <link rel="icon" type="image/svg+xml" href="images/favicon.svg">
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <?php if (isset($pageCSS)): ?>
        <link rel="stylesheet" href="css/<?php echo htmlspecialchars($pageCSS); ?>?v=<?php echo time(); ?>">
    <?php endif; ?>
</head>
<body>
    <nav class="navbar">
        <div class="container nav-container">
            <a href="index.php" class="nav-logo">
                <svg class="nav-logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <path d="M9 22V12h6v10"/>
                </svg>
                SmartHome
            </a>
            <button class="nav-toggle" id="navToggle" aria-label="Toggle menu">☰</button>
            <ul class="nav-links" id="navLinks">
                <li><a href="index.php">Home</a></li>
                <li><a href="products.php">Products</a></li>
                <li>
                    <a href="cart.php" class="cart-link">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                        </svg>
                        Cart
                        <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                            <span class="cart-badge"><?php echo array_sum($_SESSION['cart']); ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="orders.php">My Orders</a></li>
                    <li><a href="profile.php"><?php echo htmlspecialchars($_SESSION['username']); ?></a></li>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li><a href="admin.php" class="admin-link">Admin</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php" class="btn-nav btn-logout">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php" class="btn-nav btn-register">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo htmlspecialchars($_SESSION['message_type'] ?? 'info'); ?>">
            <div class="container">
                <?php echo htmlspecialchars($_SESSION['message']); ?>
                <button class="alert-close" onclick="this.parentElement.parentElement.remove()">✕</button>
            </div>
        </div>
        <?php
        unset($_SESSION['message'], $_SESSION['message_type']);
        ?>
    <?php endif; ?>
    <main>
