<?php

$pageTitle = 'My Orders';
$pageCSS = 'orders.css';
require_once 'includes/db-connection.php';
require_once 'includes/auth-check.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
requireLogin();
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

require_once 'includes/header.inc.php';
?>

<div class="container">
    <h2 class="section-title">📋 My Orders</h2>
    <?php if (count($orders) > 0): ?>
        <div class="orders-list">
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <span>Order #<?php echo $order['id']; ?></span>
                        <span><?php echo date('M d, Y - H:i', strtotime($order['created_at'])); ?></span>
                        <span class="status-badge status-<?php echo $order['status']; ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                        
                        <span><strong>Total: &euro;<?php echo number_format($order['total'], 2); ?></strong></span>
                    </div>
                    <div class="order-items">
                        <?php
                        $itemStmt = $pdo->prepare("SELECT oi.*, p.name AS product_name 
                                                    FROM order_items oi 
                                                    LEFT JOIN products p ON oi.product_id = p.id 
                                                    WHERE oi.order_id = ?");
                        $itemStmt->execute([$order['id']]);
                        $items = $itemStmt->fetchAll();
                        ?>
                        <table>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['product_name'] ?? 'Deleted Product'); ?></td>
                                    <td>Qty: <?php echo $item['quantity']; ?></td>
                                    <td>&euro;<?php echo number_format($item['price'], 2); ?> each</td>
                                    <td><strong>&euro;<?php echo number_format($item['price'] * $item['quantity'], 2); ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php else: ?>
        <div class="empty-state">
            <span class="empty-icon">📦</span>
            <h2>No Orders Yet</h2>
            <p>You haven't placed any orders. Start shopping!</p>
            <a href="products.php" class="btn btn-primary" style="margin-top:16px;">Browse Products</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.inc.php'; ?>
