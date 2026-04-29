<?php

$pageTitle = 'Edit Product';
$pageCSS = 'admin.css';
require_once 'includes/db-connection.php';
require_once 'includes/auth-check.php';
require_once 'includes/logger.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
requireAdmin();

$error = '';
$isEdit = false;
$product = ['name'=>'', 'category_id'=>'', 'price'=>'', 'description'=>'', 'stock'=>0, 'image'=>''];
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([(int)$_GET['id']]);
    $product = $stmt->fetch();
    
    if ($product) {
        $isEdit = true;
    } else {
        $_SESSION['message'] = 'Product not found.';
        $_SESSION['message_type'] = 'error';
        header('Location: admin-products.php');
        exit;
    }
}
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $categoryId = isset($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $stock = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;
    $image = isset($_POST['image']) ? trim($_POST['image']) : 'placeholder.png';
    if (empty($name)) {
        $error = 'Product name is required.';
    } elseif (strlen($name) > 100) {
        $error = 'Product name must be at most 100 characters.';
    } elseif ($price <= 0 || $price > 99999.99) {
        $error = 'Price must be between €0.01 and €99,999.99.';
    } elseif ($stock < 0 || $stock > 9999) {
        $error = 'Stock must be between 0 and 9,999.';
    } elseif (strlen($description) > 2000) {
        $error = 'Description must be at most 2,000 characters.';
    } elseif (strlen($image) > 255) {
        $error = 'Image filename must be at most 255 characters.';
    } else {
        if ($isEdit) {
            $stmt = $pdo->prepare("UPDATE products SET name=?, category_id=?, price=?, description=?, stock=?, image=? WHERE id=?");
            $stmt->execute([$name, $categoryId, $price, $description, $stock, $image, $product['id']]);
            
            logAction($pdo, $_SESSION['user_id'], 'PRODUCT_UPDATED', "Updated product: $name");
            $_SESSION['message'] = 'Product updated successfully!';
        } else {
            $stmt = $pdo->prepare("INSERT INTO products (name, category_id, price, description, stock, image) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$name, $categoryId, $price, $description, $stock, $image]);
            
            logAction($pdo, $_SESSION['user_id'], 'PRODUCT_CREATED', "Created product: $name");
            $_SESSION['message'] = 'Product created successfully!';
        }
        $_SESSION['message_type'] = 'success';
        header('Location: admin-products.php');
        exit;
    }
    $product = ['name'=>$name, 'category_id'=>$categoryId, 'price'=>$price, 
                'description'=>$description, 'stock'=>$stock, 'image'=>$image];
    if ($isEdit) $product['id'] = $_GET['id'];
}

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
            <h1><?php echo $isEdit ? '✏️ Edit Product' : '➕ Add New Product'; ?></h1>
            <?php if (!empty($error)): ?>
                <div class="server-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="form-container" style="max-width:600px;">
                <form id="productForm" method="POST" novalidate>
                    
                    <div class="form-group">
                        <label for="name">Product Name</label>
                        <input type="text" id="name" name="name" maxlength="100" value="<?php echo htmlspecialchars($product['name']); ?>">
                        <span class="form-error">Error</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="category_id">Category</label>
                        <select id="category_id" name="category_id">
                            <option value="">-- Select Category --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" 
                                    <?php echo ($product['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Price (&euro;)</label>
                        <input type="number" id="price" name="price" step="0.01" min="0.01" max="99999.99"
                               value="<?php echo $product['price']; ?>">
                        <span class="form-error">Error</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="stock">Stock Quantity</label>
                        <input type="number" id="stock" name="stock" min="0" max="9999"
                               value="<?php echo $product['stock']; ?>">
                        <span class="form-error">Error</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="image">Image Filename</label>
                        <input type="text" id="image" name="image" placeholder="e.g., product.jpg" maxlength="255"
                               value="<?php echo htmlspecialchars($product['image']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" maxlength="2000"><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">
                        <?php echo $isEdit ? 'Update Product' : 'Create Product'; ?>
                    </button>
                    
                    <a href="admin-products.php" class="btn btn-outline btn-block" style="margin-top:10px;">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.inc.php'; ?>
