<?php

$pageTitle = 'Checkout';
$pageCSS = 'checkout.css';
require_once 'includes/db-connection.php';
require_once 'includes/auth-check.php';
require_once 'includes/logger.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
requireLogin();
if (!isset($_SESSION['cart']) || count($_SESSION['cart']) === 0) {
    $_SESSION['message'] = 'Your cart is empty.';
    $_SESSION['message_type'] = 'error';
    header('Location: cart.php');
    exit;
}

$error = '';
$ids = array_keys($_SESSION['cart']);
$placeholders = implode(',', array_fill(0, count($ids), '?'));

$stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
$stmt->execute($ids);
$products = $stmt->fetchAll();

$cartItems = [];
$total = 0;
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = isset($_POST['fullname']) ? trim($_POST['fullname']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $city = isset($_POST['city']) ? trim($_POST['city']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    if (empty($fullname) || empty($address) || empty($city) || empty($phone)) {
        $error = 'Please fill in all shipping fields.';
    }
    elseif (strlen($fullname) > 100) {
        $error = 'Name must be at most 100 characters.';
    } elseif (strlen($address) > 255) {
        $error = 'Address must be at most 255 characters.';
    } elseif (strlen($city) > 100) {
        $error = 'City must be at most 100 characters.';
    } elseif (strlen($phone) > 20) {
        $error = 'Phone must be at most 20 characters.';
    }
    else {
        $shippingAddress = "$fullname\n$address\n$city\nPhone: $phone";

        // Use transaction to ensure order and stock update are processed together
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, status, shipping_address) VALUES (?, ?, 'pending', ?)");
            $stmt->execute([$_SESSION['user_id'], $total, $shippingAddress]);
            $orderId = $pdo->lastInsertId();
            foreach ($cartItems as $item) {
                $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                $stmt->execute([$orderId, $item['product']['id'], $item['quantity'], $item['product']['price']]);
                $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $stmt->execute([$item['quantity'], $item['product']['id']]);
            }
            $pdo->commit();
            logAction($pdo, $_SESSION['user_id'], 'ORDER_PLACED', "Order #$orderId placed, total: €$total");
            $_SESSION['cart'] = [];
            $_SESSION['message'] = "Order #$orderId placed successfully! Thank you for your purchase.";
            $_SESSION['message_type'] = 'success';
            header('Location: orders.php');
            exit;

        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'An error occurred while placing your order. Please try again.';
            error_log("Checkout error for user {$_SESSION['user_id']}: " . $e->getMessage());
        }
    }
}

require_once 'includes/header.inc.php';
?>

<div class="container">
    <h2 class="section-title">Checkout</h2>
    <?php if (!empty($error)): ?>
        <div class="server-error" style="max-width:800px;margin:0 auto 20px;"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="checkout-layout">
        <div class="checkout-form">
            <div class="form-container" style="max-width:100%;">
                <h2>📦 Shipping Information</h2>
                <form id="checkoutForm" method="POST" action="checkout.php" novalidate>

                    <div class="form-group">
                        <label for="fullname">Full Name</label>
                        <input type="text" id="fullname" name="fullname" placeholder="John Doe" maxlength="100"
                            value="<?php echo isset($fullname) ? htmlspecialchars($fullname) : ''; ?>">
                        <span class="form-error">Error</span> 
                    </div>

                    <div class="form-group">
                        <label for="address">Street Address</label>
                        <input type="text" id="address" name="address" placeholder="123 Main Street" maxlength="255"
                            value="<?php echo isset($address) ? htmlspecialchars($address) : ''; ?>">
                        <span class="form-error">Error</span>
                    </div>

                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text" id="city" name="city" placeholder="New York" maxlength="100"
                            value="<?php echo isset($city) ? htmlspecialchars($city) : ''; ?>">
                        <span class="form-error">Error</span>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="text" id="phone" name="phone" placeholder="+1 555 123 4567" maxlength="20"
                            value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>">
                        <span class="form-error">Error</span>
                    </div>

                    <button type="submit" class="btn btn-success btn-block">Place Order</button>
                </form>
            </div>
        </div>
        <div class="checkout-summary">
            <div class="summary-box">
                <h3>Order Summary</h3>
                <?php foreach ($cartItems as $item): ?>
                    <div class="summary-item">
                        <span><?php echo htmlspecialchars($item['product']['name']); ?> ×
                            <?php echo $item['quantity']; ?></span>
                        <span>&euro;<?php echo number_format($item['subtotal'], 2); ?></span>
                    </div>
                <?php endforeach; ?>
                <div class="summary-total">
                    <span>Total</span>
                    <span>&euro;<?php echo number_format($total, 2); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.inc.php'; ?>