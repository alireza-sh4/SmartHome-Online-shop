<?php

$pageTitle = 'Manage Orders';
$pageCSS = 'admin.css';
require_once 'includes/db-connection.php';
require_once 'includes/auth-check.php';
require_once 'includes/logger.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
requireAdmin();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = (int)$_POST['order_id'];
    $newStatus = $_POST['new_status'];
    $validStatuses = ['pending', 'processing', 'shipped', 'delivered'];

    if (in_array($newStatus, $validStatuses)) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $orderId]);
        logAction($pdo, $_SESSION['user_id'], 'ORDER_STATUS_UPDATED', "Order #$orderId status changed to $newStatus");
        $_SESSION['message'] = "Order #$orderId status updated to $newStatus.";
        $_SESSION['message_type'] = 'success';
    }
    header('Location: admin-orders.php');
    exit;
}
$orders = $pdo->query("SELECT o.*, u.username FROM orders o 
                        LEFT JOIN users u ON o.user_id = u.id 
                        ORDER BY o.created_at DESC")->fetchAll();

require_once 'includes/header.inc.php';
?>

<div class="container">
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <h3>⚙️ Admin Panel</h3>
            <ul>
                <li><a href="admin.php">📊 Dashboard</a></li>
                <li><a href="admin-products.php">📦 Products</a></li>
                <li><a href="admin-users.php">👥 Users</a></li>
                <li><a href="admin-orders.php" class="active">🛒 Orders</a></li>
                <li><a href="admin-logs.php">📝 Activity Logs</a></li>
            </ul>
        </aside>
        <div class="admin-content">
            <h1>🛒 Manage Orders</h1>

            <?php if (count($orders) > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr><th>Order #</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th><th>Update Status</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['username'] ?? 'Unknown'); ?></td>
                                <td>&euro;<?php echo number_format($order['total'], 2); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $order['status']; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                
                                <td><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                                <td>
                                    <form method="POST" style="display:flex;gap:6px;align-items:center;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <select name="new_status" style="padding:5px 8px;border-radius:6px;border:2px solid #dfe6e9;font-size:.85rem;">
                                            <?php foreach (['pending','processing','shipped','delivered'] as $status): ?>
                                                <option value="<?php echo $status; ?>" <?php echo $order['status'] === $status ? 'selected' : ''; ?>>
                                                    <?php echo ucfirst($status); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        
                                        <button type="submit" name="update_status" class="btn btn-primary btn-sm">Update</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color:#636e72;">No orders found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.inc.php'; ?>
