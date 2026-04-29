<?php

$pageTitle = 'Shopping Cart';
$pageCSS = 'cart.css';
require_once 'includes/db-connection.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_cart']) || isset($_POST['checkout'])) {
        if (isset($_POST['quantities']) && is_array($_POST['quantities'])) {
            foreach ($_POST['quantities'] as $pid => $qty) {
                $pid = (int)$pid;
                $qty = (int)$qty;
                
                if ($qty <= 0) {
                    unset($_SESSION['cart'][$pid]);
                } else {
                    $_SESSION['cart'][$pid] = $qty;
                }
            }
        }
        
        if (isset($_POST['checkout'])) {
            header('Location: checkout.php');
            exit;
        } else {
            $_SESSION['message'] = 'Cart updated!';
            $_SESSION['message_type'] = 'success';
        }
    }
    if (isset($_POST['remove_item'])) {
        $removeId = (int)$_POST['remove_item'];
        unset($_SESSION['cart'][$removeId]);
        
        $_SESSION['message'] = 'Item removed from cart.';
        $_SESSION['message_type'] = 'info';
    }
    if (isset($_POST['clear_cart'])) {
        $_SESSION['cart'] = [];
        $_SESSION['message'] = 'Cart cleared.';
        $_SESSION['message_type'] = 'info';
    }
    header('Location: cart.php');
    exit;
}

$cartItems = [];
$total = 0;
if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT p.*, c.icon AS category_icon FROM products p 
                           LEFT JOIN categories c ON p.category_id = c.id 
                           WHERE p.id IN ($placeholders)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll();
    foreach ($products as $product) {
        $qty = $_SESSION['cart'][$product['id']];
        $subtotal = $product['price'] * $qty;
        $total += $subtotal;
        $cartItems[] = [
            'product' => $product,
            'quantity' => $qty,
            'subtotal' => $subtotal
        ];
    }
}

require_once 'includes/header.inc.php';
?>

<div class="container">
    <h2 class="section-title">🛒 Shopping Cart</h2>

    <?php if (count($cartItems) > 0): ?>
        <form method="POST" action="cart.php">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $item): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($item['product']['name']); ?></strong>
                            </td>
                            <td>&euro;<?php echo number_format($item['product']['price'], 2); ?></td>
                            <td>
                                <input type="number" name="quantities[<?php echo $item['product']['id']; ?>]" 
                                       value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo min($item['product']['stock'], 999); ?>">
                            </td>
                            <td><strong>&euro;<?php echo number_format($item['subtotal'], 2); ?></strong></td>
                            <td>
                                <button type="submit" name="remove_item" value="<?php echo $item['product']['id']; ?>" 
                                        class="btn btn-danger btn-sm">Remove</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="cart-summary">
                <div class="total">Total: &euro;<?php echo number_format($total, 2); ?></div>
            </div>
            <div class="cart-actions">
                <button type="submit" name="clear_cart" class="btn btn-outline">Clear Cart</button>
                <button type="submit" name="update_cart" class="btn btn-secondary">Update Cart</button>
                <button type="submit" name="checkout" class="btn btn-primary">Proceed to Checkout →</button>
            </div>
        </form>

    <?php else: ?>
        <div class="empty-state">
            <span class="empty-icon">🛒</span>
            <h2>Your Cart is Empty</h2>
            <p>Looks like you haven't added any products yet.</p>
            <a href="products.php" class="btn btn-primary" style="margin-top:16px;">Browse Products</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.inc.php'; ?>
