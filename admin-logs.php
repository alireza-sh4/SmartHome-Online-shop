<?php

$pageTitle = 'Activity Logs';
$pageCSS = 'admin.css';
require_once 'includes/db-connection.php';
require_once 'includes/auth-check.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
requireAdmin();
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;
$totalLogs = $pdo->query("SELECT COUNT(*) FROM logs")->fetchColumn();
$totalPages = ceil($totalLogs / $perPage);
$stmt = $pdo->prepare("SELECT l.*, u.username FROM logs l 
                        LEFT JOIN users u ON l.user_id = u.id 
                        ORDER BY l.created_at DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $perPage, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$logs = $stmt->fetchAll();
$fileLog = '';
$logFile = __DIR__ . '/logs/activity.txt';

if (file_exists($logFile)) {
    $fileLog = file_get_contents($logFile);
    if (empty($fileLog)) {
        $fileLog = '(No file log entries yet)';
    }
} else {
    $fileLog = '(Log file not found at ' . htmlspecialchars($logFile) . ')';
}

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
                <li><a href="admin-orders.php">🛒 Orders</a></li>
                <li><a href="admin-logs.php" class="active">📝 Activity Logs</a></li>
            </ul>
        </aside>
        <div class="admin-content">
            <h1>📝 Activity Logs</h1>
            <h2 style="margin-bottom:16px;">Database Logs</h2>
            <table class="data-table" style="margin-bottom:20px;">
                <thead>
                    <tr><th>ID</th><th>User</th><th>Action</th><th>Details</th><th>Timestamp</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?php echo $log['id']; ?></td>
                            <td><?php echo htmlspecialchars($log['username'] ?? 'System'); ?></td>
                            <td><?php echo htmlspecialchars($log['action']); ?></td>
                            <td><?php echo htmlspecialchars($log['details']); ?></td>
                            <td><?php echo date('M d, Y H:i:s', strtotime($log['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    
                    <?php if (count($logs) === 0): ?>
                        <tr><td colspan="5" style="text-align:center;color:#636e72;">No log entries yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i === $page): ?>
                            <span class="active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="admin-logs.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
            <h2 style="margin:30px 0 16px;">File Log (activity.txt)</h2>
            <div class="log-viewer"><?php echo htmlspecialchars($fileLog); ?></div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.inc.php'; ?>
