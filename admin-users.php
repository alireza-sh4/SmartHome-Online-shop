<?php

$pageTitle = 'Manage Users';
$pageCSS = 'admin.css';
require_once 'includes/db-connection.php';
require_once 'includes/auth-check.php';
require_once 'includes/logger.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
requireAdmin();
if (isset($_GET['toggle_role'])) {
    $toggleId = (int)$_GET['toggle_role'];
    if ($toggleId !== $_SESSION['user_id']) {
        $stmt = $pdo->prepare("SELECT username, role FROM users WHERE id = ?");
        $stmt->execute([$toggleId]);
        $targetUser = $stmt->fetch();

        if ($targetUser) {
            $newRole = ($targetUser['role'] === 'admin') ? 'user' : 'admin';
            $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->execute([$newRole, $toggleId]);
            logAction($pdo, $_SESSION['user_id'], 'ROLE_CHANGED', "Changed {$targetUser['username']} role to $newRole");
            
            $_SESSION['message'] = "User role updated to $newRole.";
            $_SESSION['message_type'] = 'success';
        }
    } else {
        $_SESSION['message'] = "You cannot change your own role.";
        $_SESSION['message_type'] = 'error';
    }
    header('Location: admin-users.php');
    exit;
}
$users = $pdo->query("SELECT u.*, (SELECT COUNT(*) FROM orders WHERE user_id = u.id) AS order_count 
                       FROM users u ORDER BY u.created_at DESC")->fetchAll();

require_once 'includes/header.inc.php';
?>

<div class="container">
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <h3>⚙️ Admin Panel</h3>
            <ul>
                <li><a href="admin.php">📊 Dashboard</a></li>
                <li><a href="admin-products.php">📦 Products</a></li>
                <li><a href="admin-users.php" class="active">👥 Users</a></li>
                <li><a href="admin-orders.php">🛒 Orders</a></li>
                <li><a href="admin-logs.php">📝 Activity Logs</a></li>
            </ul>
        </aside>
        <div class="admin-content">
            <h1>👥 Manage Users</h1>
            
            <table class="data-table">
                <thead>
                    <tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Orders</th><th>Joined</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?php echo $u['id']; ?></td>
                            <td><?php echo htmlspecialchars($u['username']); ?></td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $u['role'] === 'admin' ? 'shipped' : 'processing'; ?>">
                                    <?php echo ucfirst($u['role']); ?>
                                </span>
                            </td>
                            
                            <td><?php echo $u['order_count']; ?></td>
                            <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                            <td>
                                <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                                    <a href="admin-users.php?toggle_role=<?php echo $u['id']; ?>" 
                                       class="btn btn-sm <?php echo $u['role'] === 'admin' ? 'btn-danger' : 'btn-success'; ?>"
                                       onclick="return confirm('Change this user\'s role?')">
                                        <?php echo $u['role'] === 'admin' ? 'Demote' : 'Promote'; ?>
                                    </a>
                                <?php else: ?>
                                    <span style="color:#b2bec3;">You</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.inc.php'; ?>
