<?php

require_once 'includes/db-connection.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($productId <= 0) {
    header('Location: products.php');
    exit;
}
$stmt = $pdo->prepare("SELECT p.*, c.name AS category_name, c.icon AS category_icon 
                        FROM products p LEFT JOIN categories c ON p.category_id = c.id 
                        WHERE p.id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch();
if (!$product) {
    $_SESSION['message'] = 'Product not found.';
    $_SESSION['message_type'] = 'error';
    header('Location: products.php');
    exit;
}
$pageTitle = $product['name'];
$pageCSS = 'product-detail.css';
$relatedStmt = $pdo->prepare("SELECT p.*, c.name AS category_name 
                               FROM products p LEFT JOIN categories c ON p.category_id = c.id 
                               WHERE p.category_id = ? AND p.id != ? 
                               ORDER BY RAND() LIMIT 4");
$relatedStmt->execute([$product['category_id'], $productId]);
$relatedProducts = $relatedStmt->fetchAll();
$specs = [];
switch ($product['category_icon'] ?? '') {
    case 'lighting':
        $specs = [
            'Connectivity' => 'WiFi 2.4GHz + Bluetooth',
            'Compatibility' => 'Alexa, Google Home, HomeKit',
            'Power' => '9W (60W equivalent)',
            'Colour Range' => '16 Million RGB + Warm/Cool White',
            'Lifespan' => '25,000 hours',
            'Warranty' => '2 Years'
        ];
        break;
    case 'security':
        $specs = [
            'Resolution' => '1080p Full HD / 2K',
            'Connectivity' => 'WiFi 2.4GHz / 5GHz',
            'Night Vision' => 'IR up to 10m',
            'Storage' => 'Cloud + microSD (up to 128GB)',
            'Weather Rating' => 'IP65 Weatherproof',
            'Warranty' => '2 Years'
        ];
        break;
    case 'climate':
        $specs = [
            'Display' => 'LCD / E-Ink Touch Display',
            'Connectivity' => 'WiFi + Bluetooth',
            'Sensors' => 'Temperature, Humidity, Air Quality',
            'Compatibility' => 'Alexa, Google Home',
            'Power' => 'Mains / Battery (up to 2 years)',
            'Warranty' => '2 Years'
        ];
        break;
    case 'entertainment':
        $specs = [
            'Audio' => '360° Omnidirectional / Stereo',
            'Connectivity' => 'WiFi, Bluetooth 5.0',
            'Voice Assistant' => 'Built-in (Alexa / Google)',
            'Power' => 'Mains Powered',
            'Display' => 'Touch Panel / 8" Touchscreen',
            'Warranty' => '2 Years'
        ];
        break;
    case 'sensors':
        $specs = [
            'Connectivity' => 'Zigbee / WiFi / Bluetooth',
            'Battery' => 'CR2032 (up to 2 years)',
            'Range' => 'Up to 30m indoors',
            'Alerts' => 'Push Notification + Alarm',
            'Size' => 'Compact (40 x 40 x 15 mm)',
            'Warranty' => '2 Years'
        ];
        break;
    default:
        $specs = [
            'Connectivity' => 'WiFi 2.4GHz',
            'Compatibility' => 'Alexa, Google Home',
            'Power' => 'Mains / USB-C',
            'Warranty' => '2 Years'
        ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] += $quantity;
    } else {
        $_SESSION['cart'][$productId] = $quantity;
    }
    $_SESSION['message'] = htmlspecialchars($product['name']) . ' added to cart!';
    $_SESSION['message_type'] = 'success';
    header('Location: product-detail.php?id=' . $productId);
    exit;
}
require_once 'includes/header.inc.php';
?>

<div class="container">
    <div class="breadcrumb">
        <a href="index.php">Home</a> <span>›</span>
        <a href="products.php">Products</a> <span>›</span>
        <a href="products.php?category=<?php echo $product['category_id']; ?>"><?php echo htmlspecialchars($product['category_name']); ?></a> <span>›</span>
        <?php echo htmlspecialchars($product['name']); ?>
    </div>

    <div class="product-detail-layout">
        <div class="product-detail-main">
            
            <div class="product-detail">
                <div class="product-detail-image">
                    <?php if (!empty($product['image']) && file_exists('images/' . $product['image'])): ?>
                        <img src="images/<?php echo htmlspecialchars($product['image']); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <?php else: ?>
                        <div class="product-placeholder cat-<?php echo $product['category_id']; ?>">
                            <span class="prod-icon prod-icon-<?php echo htmlspecialchars($product['category_icon'] ?? 'default'); ?>"></span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="product-detail-info">
                    <span class="category-tag"><?php echo htmlspecialchars($product['category_name']); ?></span>
                    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                    <div class="price">&euro;<?php echo number_format($product['price'], 2); ?></div>
                    <p class="description"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                    <?php if ($product['stock'] > 0): ?>
                        <p class="stock <?php echo $product['stock'] < 10 ? 'stock-low' : ''; ?>" style="margin-bottom:20px;">
                            <?php echo $product['stock'] < 10 ? 'Only ' . $product['stock'] . ' left in stock!' : 'In Stock (' . $product['stock'] . ' available)'; ?>
                        </p>
                        <form method="POST" action="product-detail.php?id=<?php echo $productId; ?>">
                            <div class="quantity-selector">
                                <label for="quantity">Quantity:</label>
                                <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo min($product['stock'], 999); ?>">
                            </div>
                            <button type="submit" name="add_to_cart" class="btn btn-primary" style="font-size:1.05rem;padding:14px 36px;">
                                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;margin-right:8px;">
                                    <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                                </svg>
                                Add to Cart
                            </button>
                        </form>
                        
                    <?php else: ?>
                        <p class="stock stock-low" style="margin-bottom:20px; font-size:1.1rem;">Out of Stock</p>
                        <button class="btn btn-secondary" disabled>Currently Unavailable</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php if (!empty($specs)): ?>
            <div class="product-specs">
                <h3>Technical Specifications</h3>
                <table class="specs-table">
                    <?php foreach ($specs as $label => $value): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($label); ?></td>
                            <td><?php echo htmlspecialchars($value); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
            <?php endif; ?>
        </div>
        <aside class="product-sidebar">
            <div class="sidebar-box">
                <h3>Shipping &amp; Returns</h3>
                <p style="font-size:.9rem;color:var(--text-mid);margin-bottom:10px;">Free shipping on orders over &euro;50. Standard delivery in 2-4 business days.</p>
                <p style="font-size:.9rem;color:var(--text-mid);">30-day returns policy. 2-year manufacturer warranty included.</p>
            </div>
            <?php if (!empty($relatedProducts)): ?>
            <div class="sidebar-box">
                <h3>You May Also Like</h3>
                <?php foreach ($relatedProducts as $rp): ?>
                    <a href="product-detail.php?id=<?php echo $rp['id']; ?>" class="sidebar-product" style="text-decoration:none;">
                        <?php if (!empty($rp['image']) && file_exists('images/' . $rp['image'])): ?>
                            <img src="images/<?php echo htmlspecialchars($rp['image']); ?>" alt="<?php echo htmlspecialchars($rp['name']); ?>">
                        <?php else: ?>
                            <div style="width:60px;height:60px;border-radius:8px;background:var(--accent-light);display:flex;align-items:center;justify-content:center;">
                                <span class="cat-icon cat-icon-<?php echo htmlspecialchars($rp['category_name'] ?? 'default'); ?>" style="width:24px;height:24px;"></span>
                            </div>
                        <?php endif; ?>
                        <div class="sp-info">
                            <h4><?php echo htmlspecialchars($rp['name']); ?></h4>
                            <div class="price">&euro;<?php echo number_format($rp['price'], 2); ?></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </aside>
    </div>
</div>

<?php require_once 'includes/footer.inc.php'; ?>
