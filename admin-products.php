<?php

$pageTitle = 'Manage Products';
$pageCSS = 'admin.css';
require_once 'includes/db-connection.php';
require_once 'includes/auth-check.php';
require_once 'includes/logger.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
requireAdmin();
if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    $stmt = $pdo->prepare("SELECT name FROM products WHERE id = ?");
    $stmt->execute([$deleteId]);
    $product = $stmt->fetch();

    if ($product) {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$deleteId]);
        logAction($pdo, $_SESSION['user_id'], 'PRODUCT_DELETED', 'Deleted product: ' . $product['name']);
        $_SESSION['message'] = 'Product deleted successfully.';
        $_SESSION['message_type'] = 'success';
    }
    header('Location: admin-products.php');
    exit;
}
$stmt = $pdo->query("SELECT p.*, c.name AS category_name 
                      FROM products p LEFT JOIN categories c ON p.category_id = c.id 
                      ORDER BY p.id DESC");
$products = $stmt->fetchAll();

require_once 'includes/header.inc.php';
?>

<div class="container">
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <h3>⚙️ Admin Panel</h3>
            <ul>
                <li><a href="admin.php">📊 Dashboard</a></li>
                <li><a href="admin-products.php" class="active">📦 Products</a></li>
                <li><a href="admin-users.php">👥 Users</a></li>
                <li><a href="admin-orders.php">🛒 Orders</a></li>
                <li><a href="admin-logs.php">📝 Activity Logs</a></li>
            </ul>
        </aside>
        <div class="admin-content">
            <h1>📦 Manage Products</h1>
            <a href="admin-product-edit.php" class="btn btn-primary" style="margin-bottom:20px;">+ Add New Product</a>
            <table class="data-table">
                <thead>
                    <tr><th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <td><?php echo $p['id']; ?></td>
                            <td><?php echo htmlspecialchars($p['name']); ?></td>
                            <td><?php echo htmlspecialchars($p['category_name'] ?? 'None'); ?></td>
                            <td>&euro;<?php echo number_format($p['price'], 2); ?></td>
                            <td class="<?php echo $p['stock'] < 10 ? 'stock-low' : ''; ?>">
                                <?php echo $p['stock']; ?>
                            </td>
                            
                            <td class="actions">
                                <a href="admin-product-edit.php?id=<?php echo $p['id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                                <a href="admin-products.php?delete=<?php echo $p['id']; ?>" 
                                   class="btn btn-danger btn-sm" 
                                   onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.inc.php'; ?>
