<?php

$pageTitle = 'Admin Dashboard';
$pageCSS = 'admin.css';
require_once 'includes/db-connection.php';
require_once 'includes/auth-check.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
requireAdmin();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalRevenue = $pdo->query("SELECT COALESCE(SUM(total), 0) FROM orders")->fetchColumn();
$recentOrders = $pdo->query("SELECT o.*, u.username FROM orders o 
                              LEFT JOIN users u ON o.user_id = u.id 
                              ORDER BY o.created_at DESC LIMIT 5")->fetchAll();
$recentLogs = $pdo->query("SELECT l.*, u.username FROM logs l 
                            LEFT JOIN users u ON l.user_id = u.id 
                            ORDER BY l.created_at DESC LIMIT 10")->fetchAll();

require_once 'includes/header.inc.php';
?>

<div class="container">
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <h3>⚙️ Admin Panel</h3>
            <ul>
                <li><a href="admin.php" class="active">📊 Dashboard</a></li>
                <li><a href="admin-products.php">📦 Products</a></li>
                <li><a href="admin-users.php">👥 Users</a></li>
                <li><a href="admin-orders.php">🛒 Orders</a></li>
                <li><a href="admin-logs.php">📝 Activity Logs</a></li>
            </ul>
        </aside>
        <div class="admin-content">
            <h1>Dashboard</h1>
            <div class="stats-grid">
                <div class="stat-card">
                    <span class="stat-icon">👥</span>
                    <div class="stat-number"><?php echo $totalUsers; ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
                <div class="stat-card">
                    <span class="stat-icon">📦</span>
                    <div class="stat-number"><?php echo $totalProducts; ?></div>
                    <div class="stat-label">Products</div>
                </div>
                <div class="stat-card">
                    <span class="stat-icon">🛒</span>
                    <div class="stat-number"><?php echo $totalOrders; ?></div>
                    <div class="stat-label">Orders</div>
                </div>
                <div class="stat-card">
                    <span class="stat-icon">💰</span>
                    <div class="stat-number">&euro;<?php echo number_format($totalRevenue, 2); ?></div>
                    <div class="stat-label">Total Revenue</div>
                </div>
            </div>
            <h2 style="margin-bottom:16px;">Recent Orders</h2>
            <?php if (count($recentOrders) > 0): ?>
                <table class="data-table" style="margin-bottom:30px;">
                    <thead>
                        <tr><th>Order #</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['username'] ?? 'Unknown'); ?></td>
                                <td>&euro;<?php echo number_format($order['total'], 2); ?></td>
                                <td><span class="status-badge status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                                <td><?php echo date('M d, H:i', strtotime($order['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color:#636e72;margin-bottom:30px;">No orders yet.</p>
            <?php endif; ?>
            <h2 style="margin-bottom:16px;">Recent Activity</h2>
            <table class="data-table">
                <thead>
                    <tr><th>User</th><th>Action</th><th>Details</th><th>Time</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($recentLogs as $log): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($log['username'] ?? 'System'); ?></td>
                            <td><?php echo htmlspecialchars($log['action']); ?></td>
                            <td><?php echo htmlspecialchars($log['details']); ?></td>
                            <td><?php echo date('M d, H:i', strtotime($log['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.inc.php'; ?>
