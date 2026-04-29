<?php
$pageTitle = 'Home';
$pageCSS = 'home.css';
require_once 'includes/db-connection.php';
require_once 'includes/header.inc.php';
$stmt = $pdo->query("SELECT p.*, c.name AS category_name, c.icon AS category_icon 
                      FROM products p 
                      LEFT JOIN categories c ON p.category_id = c.id 
                      ORDER BY p.created_at DESC LIMIT 8");
$featuredProducts = $stmt->fetchAll();
$stmt = $pdo->query("SELECT c.*, COUNT(p.id) AS product_count 
                      FROM categories c 
                      LEFT JOIN products p ON c.id = p.category_id 
                      GROUP BY c.id");
$categories = $stmt->fetchAll();
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalCategories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$topProducts = $pdo->query("SELECT p.*, c.name AS category_name, c.icon AS category_icon,
                            COALESCE(SUM(oi.quantity), 0) AS total_sold
                            FROM products p
                            LEFT JOIN categories c ON p.category_id = c.id
                            LEFT JOIN order_items oi ON p.id = oi.product_id
                            GROUP BY p.id
                            ORDER BY total_sold DESC, p.created_at DESC
                            LIMIT 4")->fetchAll();
?>
<section class="hero">
    <div class="container">
        <h1>🏠 Make Your Home Smarter</h1>
        <p>Discover the latest smart home technology. From intelligent lighting to advanced security systems — everything you need to transform your living space.</p>
        <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
            <a href="products.php" class="btn btn-primary">Shop All Products</a>
            <a href="#categories" class="btn btn-outline" style="border-color:#fff;color:#fff;">Browse Categories</a>
        </div>
        <div class="hero-stats">
            <div class="hero-stat">
                <span class="num"><?php echo $totalProducts; ?>+</span>
                <span class="label">Products</span>
            </div>
            <div class="hero-stat">
                <span class="num"><?php echo $totalCategories; ?></span>
                <span class="label">Categories</span>
            </div>
            <div class="hero-stat">
                <span class="num">24/7</span>
                <span class="label">Support</span>
            </div>
            <div class="hero-stat">
                <span class="num">Free</span>
                <span class="label">Shipping 50&euro;+</span>
            </div>
        </div>
    </div>
</section>
<section id="categories">
    <div class="container">
        <h2 class="section-title">Shop by Category</h2>
        <p class="section-subtitle">Find the perfect smart devices for every room in your home</p>

        <div class="categories-grid">
            <?php foreach ($categories as $cat): ?>
                <a href="products.php?category=<?php echo $cat['id']; ?>" class="category-card">
                    <?php if (!empty($cat['image']) && file_exists('images/' . $cat['image'])): ?>
                        <div class="cat-bg" style="background-image:url('images/<?php echo htmlspecialchars($cat['image']); ?>')"></div>
                    <?php else: ?>
                        <div class="cat-bg" style="background:linear-gradient(135deg,var(--primary-dark),var(--accent))"></div>
                    <?php endif; ?>
                    
                    <div class="cat-overlay"></div> 
                    
                    <div class="cat-content">
                        <span class="cat-icon cat-icon-<?php echo htmlspecialchars($cat['icon'] ?? 'default'); ?>"></span>
                        <h3><?php echo htmlspecialchars($cat['name']); ?></h3>
                        <p><?php echo $cat['product_count']; ?> Products</p> 
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<section>
    <div class="container">
        <h2 class="section-title">🔥 Featured Products</h2>
        <p class="section-subtitle">Our latest and most popular smart home devices</p>

        <div class="product-grid">
            <?php foreach ($featuredProducts as $product): ?>
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
                        <?php if ($product['stock'] > 0): ?>
                            <span class="stock <?php echo $product['stock'] < 10 ? 'stock-low' : ''; ?>">
                                <?php echo $product['stock'] < 10 ? 'Only ' . $product['stock'] . ' left!' : 'In Stock'; ?>
                            </span>
                        <?php else: ?>
                            <span class="stock stock-low">Out of Stock</span>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <div style="text-align:center;padding:20px 0 10px;">
            <a href="products.php" class="btn btn-outline">View All Products →</a>
        </div>
    </div>
</section>
<section class="promo-banner">
    <div class="container">
        <div class="promo-inner">
            <div>
                <h2>🎉 Smart Home Starter Kit — Save 20%</h2>
                <p>Get started with our curated bundle: Smart Speaker + 2 LED Bulbs + Motion Sensor. Everything you need to begin your smart home journey.</p>
            </div>
            <a href="products.php" class="btn">Shop the Deal →</a>
        </div>
    </div>
</section>
<section>
    <div class="container">
        <h2 class="section-title">Why Choose SmartHome?</h2>
        <p class="section-subtitle">We're committed to making your home smarter, safer, and more comfortable</p>
        <div class="features-grid">
            <div class="feature-card">
                <span class="feature-icon">🚚</span>
                <h3>Free Shipping</h3>
                <p>Free delivery on all orders over &euro;50. Fast and reliable shipping to your doorstep.</p>
            </div>
            <div class="feature-card">
                <span class="feature-icon">🔧</span>
                <h3>Easy Setup</h3>
                <p>All products come with simple guides. Most devices are ready in under 5 minutes.</p>
            </div>
            <div class="feature-card">
                <span class="feature-icon">🛡️</span>
                <h3>2-Year Warranty</h3>
                <p>Every product includes a comprehensive 2-year warranty for peace of mind.</p>
            </div>
            <div class="feature-card">
                <span class="feature-icon">💬</span>
                <h3>24/7 Support</h3>
                <p>Our expert team is always ready to help with setup, troubleshooting, and more.</p>
            </div>
        </div>
    </div>
</section>
<section class="newsletter">
    <div class="container">
        <h2>📬 Stay Updated</h2>
        <p>Subscribe to our newsletter for exclusive deals, new product launches, and smart home tips.</p>
        <form class="newsletter-form" onsubmit="event.preventDefault(); alert('Thanks for subscribing!'); this.reset();">
            <input type="email" placeholder="Enter your email address" maxlength="100" required>
            <button type="submit">Subscribe</button>
        </form>
    </div>
</section>

<?php 
require_once 'includes/footer.inc.php'; 
?>
