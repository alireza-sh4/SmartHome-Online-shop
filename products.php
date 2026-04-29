<?php

$pageTitle = 'Products';
$pageCSS = 'products.css';
require_once 'includes/db-connection.php';
require_once 'includes/header.inc.php';
$categoryFilter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 8;
$offset = ($page - 1) * $perPage;
$catStmt = $pdo->query("SELECT c.*, COUNT(p.id) AS product_count 
                         FROM categories c LEFT JOIN products p ON c.id = p.category_id 
                         GROUP BY c.id ORDER BY c.name");
$categories = $catStmt->fetchAll();
$whereClause = "";
$params = [];
// Build dynamic WHERE clause based on active filters
if ($categoryFilter > 0 && !empty($searchQuery)) {
    $whereClause = "WHERE p.category_id = ? AND (p.name LIKE ? OR p.description LIKE ?)";
    $params = [
        $categoryFilter,
        "%$searchQuery%",
        "%$searchQuery%" 
    ];
} 
elseif ($categoryFilter > 0 && empty($searchQuery)) {
    $whereClause = "WHERE p.category_id = ?";
    $params = [
        $categoryFilter
    ];
} 
elseif ($categoryFilter == 0 && !empty($searchQuery)) {
    $whereClause = "WHERE (p.name LIKE ? OR p.description LIKE ?)";
    $params = [
        "%$searchQuery%",
        "%$searchQuery%" 
    ];
}
else {
    $whereClause = "";
    $params = [];
}
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM products p $whereClause");
$countStmt->execute($params);
$totalProducts = $countStmt->fetchColumn();
$totalPages = ceil($totalProducts / $perPage);
// Fetch paginated products
$sql = "SELECT p.*, c.name AS category_name, c.icon AS category_icon 
        FROM products p LEFT JOIN categories c ON p.category_id = c.id 
        $whereClause ORDER BY p.created_at DESC LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<div class="container">
    <h2 class="section-title">Our Products</h2>

    <div class="products-layout">
        <aside class="sidebar">
            <h3>Categories</h3>
            <ul>
                <li><a href="products.php" class="<?php echo $categoryFilter === 0 ? 'active' : ''; ?>">All Products</a></li>
                <?php foreach ($categories as $cat): ?>
                    <li>
                        <a href="products.php?category=<?php echo $cat['id']; ?>" 
                           class="<?php echo $categoryFilter === $cat['id'] ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($cat['name']); ?>
                            (<?php echo $cat['product_count']; ?>) 
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </aside>
        <div class="products-main">
            <form class="search-bar" method="GET" action="products.php">
                <?php if ($categoryFilter > 0): ?>
                    <input type="hidden" name="category" value="<?php echo $categoryFilter; ?>">
                <?php endif; ?>
                <input type="text" name="search" placeholder="Search products..." maxlength="100"
                       value="<?php echo htmlspecialchars($searchQuery); ?>">
                <button type="submit" class="btn btn-primary">Search</button>
            </form>

            <?php if (count($products) > 0): ?>
                <div class="product-grid">
                    <?php foreach ($products as $product): ?>
                        <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="product-card">
                            <?php if (!empty($product['image']) && file_exists('images/' . $product['image'])): ?>
                                <img src="images/<?php echo htmlspecialchars($product['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
                            <?php else: ?>
                                <div class="product-placeholder cat-<?php echo $product['category_id']; ?>">
                                    <span class="prod-icon prod-icon-<?php echo htmlspecialchars($product['category_icon'] ?? 'default'); ?>"></span>
                                </div>
                            <?php endif; ?>
                            <div class="product-info">
                                <span class="category-tag"><?php echo htmlspecialchars($product['category_name']); ?></span>
                                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                <div class="price">&euro;<?php echo number_format($product['price'], 2); ?></div>
                                <span class="stock <?php echo $product['stock'] < 10 ? 'stock-low' : ''; ?>">
                                    <?php echo $product['stock'] > 0 ? ($product['stock'] < 10 ? 'Only ' . $product['stock'] . ' left!' : 'In Stock') : 'Out of Stock'; ?>
                                </span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php
                            $queryParams = [];
                            if ($categoryFilter > 0) $queryParams['category'] = $categoryFilter;
                            if (!empty($searchQuery)) $queryParams['search'] = $searchQuery;
                            $queryParams['page'] = $i;
                            $url = 'products.php?' . http_build_query($queryParams);
                            ?>
                            <?php if ($i === $page): ?>
                                <span class="active"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="<?php echo $url; ?>"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="empty-state">
                    <span class="empty-icon">🔍</span>
                    <h2>No Products Found</h2>
                    <p>Try a different search term or browse all categories.</p>
                    <a href="products.php" class="btn btn-primary" style="margin-top:16px;">View All Products</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.inc.php'; ?>
