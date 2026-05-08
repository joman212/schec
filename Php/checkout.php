<?php
session_start();

$conn = mysqli_connect("localhost", "root", "", "schecter_db");

function json_out($data) { header('Content-Type: application/json'); echo json_encode($data); exit; }
function db_rows($conn, $sql) { return mysqli_fetch_all(mysqli_query($conn, $sql), MYSQLI_ASSOC); }

if (!$conn) {
    isset($_GET['action']) ? json_out(['success' => false, 'message' => 'DB connection failed']) : die("DB connection failed");
}

$logged_in  = isset($_SESSION['user_id']);
$user_id    = $logged_in ? (int)$_SESSION['user_id'] : 0;
$get_action = $_GET['action'] ?? '';

if (in_array($get_action, ['get_cart', 'get_orders']) && !$logged_in)
    json_out(['success' => false, 'message' => 'not_logged_in']);

if ($get_action === 'get_cart') {
    $rows = db_rows($conn, "SELECT c.quantity, p.id, p.name, p.price, p.image
                             FROM cart c JOIN products p ON c.product_id = p.id
                             WHERE c.user_id = $user_id");
    json_out(['success' => true, 'items' => array_map(fn($r) => [
        'id' => (string)$r['id'], 'name' => $r['name'],
        'price' => (float)$r['price'], 'image' => $r['image'], 'quantity' => (int)$r['quantity']
    ], $rows)]);
}

if ($get_action === 'get_orders') {
    $orders = db_rows($conn, "SELECT id, total_amount, status, created_at FROM orders
                               WHERE user_id = $user_id ORDER BY created_at DESC");
    foreach ($orders as &$o) {
        $o['total_amount'] = (float)$o['total_amount'];
        $o['items'] = db_rows($conn, "SELECT oi.quantity, oi.price, p.name, p.image
                                       FROM order_items oi JOIN products p ON oi.product_id = p.id
                                       WHERE oi.order_id = {$o['id']}");
    }
    json_out(['success' => true, 'orders' => $orders]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {

    if (!$logged_in) json_out(['success' => false, 'message' => 'not_logged_in']);

    $data = json_decode(file_get_contents('php://input'), true) ?? [];

    if (($data['action'] ?? '') === 'place_order') {
        $cart = db_rows($conn, "SELECT c.quantity, p.id, p.name, p.price
                                 FROM cart c JOIN products p ON c.product_id = p.id
                                 WHERE c.user_id = $user_id");

        if (empty($cart)) json_out(['success' => false, 'message' => 'Cart is empty']);

        $total = array_sum(array_map(fn($i) => (float)$i['price'] * (int)$i['quantity'], $cart));

        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'pending')");
        $stmt->bind_param('id', $user_id, $total);
        $stmt->execute();
        $order_id = $conn->insert_id;
        $stmt->close();

        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        foreach ($cart as $item) {
            $pid = (int)$item['id']; $qty = (int)$item['quantity']; $price = (float)$item['price'];
            $stmt->bind_param('iiid', $order_id, $pid, $qty, $price);
            $stmt->execute();
        }
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->close();

        json_out(['success' => true, 'order_id' => $order_id, 'total' => $total]);
    }

    json_out(['success' => false, 'message' => 'Unknown action']);
}

$page_items = [];
$page_total = 0.0;
if ($logged_in) {
    $page_items = db_rows($conn, "SELECT c.quantity, p.id, p.name, p.price, p.image
                                   FROM cart c JOIN products p ON c.product_id = p.id
                                   WHERE c.user_id = $user_id");
    $page_total = array_sum(array_map(fn($r) => (float)$r['price'] * (int)$r['quantity'], $page_items));
}
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schecter Guitars – Checkout</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="icon" href="../images/icon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@300;400;700&display=swap" rel="stylesheet">
</head>
<body>
<div id="myOffcanvasNav" class="oc-sidebar">
  <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
  <div class="oc-logo" style="padding:0 25px 20px;border-bottom:1px solid #333;margin-bottom:15px;">
    <img src="../images/logo.png" alt="Schecter Guitars" style="height:40px;width:auto;">
  </div>
  <a href="../index.html">Home</a>
  <a href="../php/products.php">Guitars</a>
  <a href="../php/accessories.php">Accessories</a>
  <a href="../html/about.html">About</a>
  <a href="../php/support.php">Support</a>
  <a href="../php/Contact.php">Contact</a>
  <a href="../php/login.php">Sign In</a>
  <a href="../php/cart.php" class="oc-cart">
    <img src="../images/cart.png" alt="Cart" style="width:20px;vertical-align:middle;margin-right:8px;">
    Cart <span class="cart-count">0</span>
  </a>
</div>

<header>
  <div class="logo"><img src="../images/logo.png" alt="Schecter Guitars"></div>
  <span style="font-size:30px;cursor:pointer;color:#fff;margin-right:15px;" onclick="openNav()">&#9776;</span>
</header>

<section class="checkout">
  <h1>Checkout</h1>

  <?php if (!$logged_in): ?>
    <p style="color:#E5E5E5;text-align:center;padding:2rem">
      Please <a href="../php/login.php">sign in</a> to complete your purchase.
    </p>
  <?php elseif (empty($page_items)): ?>
    <p style="color:#E5E5E5;text-align:center;padding:2rem">
      Your cart is empty. <a href="../php/products.php">Keep shopping</a>
    </p>
  <?php else: ?>

  <div id="checkoutMessage" style="display:none;padding:1rem;margin-bottom:1rem;border-radius:6px;text-align:center;font-weight:bold;"></div>

  <div class="checkout-form" id="checkoutForm">
    <h2>Shipping Information</h2>
    <input type="text"  id="co-name"    placeholder="Full Name"       required>
    <input type="text"  id="co-address" placeholder="Address"         required>
    <input type="text"  id="co-city"    placeholder="City"            required>
    <input type="text"  id="co-state"   placeholder="State/Province"  required>
    <input type="text"  id="co-postal"  placeholder="Postal Code"     required>
    <input type="text"  id="co-phone"   placeholder="Phone Number"    required>
    <input type="email" id="co-email"   placeholder="Email"           required>

    <h2>Payment Method</h2>
    <label><input type="radio" name="payment" value="credit_card" required> Credit Card</label><br>
    <label><input type="radio" name="payment" value="paypal"> PayPal</label><br>
    <label><input type="radio" name="payment" value="bank_transfer"> Bank Transfer</label><br>

    <h2>Order Summary</h2>
    <div class="checkout-order-summary">
      <?php foreach ($page_items as $item): ?>
        <div class="checkout-item-row">
          <img src="../<?= htmlspecialchars($item['image']) ?>"
               alt="<?= htmlspecialchars($item['name']) ?>"
               style="width:50px;height:50px;object-fit:cover;border-radius:4px;"
               onerror="this.src='../images/placeholder.jpg'">
          <span class="checkout-item-name"><?= htmlspecialchars($item['name']) ?></span>
          <span class="checkout-item-qty">x<?= (int)$item['quantity'] ?></span>
          <span class="checkout-item-price">$<?= number_format((float)$item['price'] * (int)$item['quantity'], 2) ?></span>
        </div>
      <?php endforeach; ?>
      <div class="checkout-total-row">
        <strong>Total: $<?= number_format($page_total, 2) ?></strong>
      </div>
    </div>
  </div>

  <button class="checkout-btn" id="placeOrderBtn" onclick="placeOrder()">Place Order</button>

  <?php endif; ?>
</section>

<footer>
  <p>&copy; 2026 Schecter Guitars. All rights reserved.</p>
  <div class="social-media-icons">
    <a href="https://www.facebook.com/SchecterGuitarResearch" target="_blank" aria-label="Facebook">
      <img src="../images/fb.gif" alt="Facebook">
    </a>
    <a href="https://twitter.com/SchecterGuitars" target="_blank" aria-label="Twitter">
      <img src="../images/twitter.png" alt="Twitter">
    </a>
    <a href="https://www.instagram.com/schecterguitarsofficial/" target="_blank" aria-label="Instagram">
      <img src="../images/instagram.png" alt="Instagram">
    </a>
    <a href="https://www.youtube.com/user/SchecterGuitars" target="_blank" aria-label="YouTube">
      <img src="../images/youtube.png" alt="YouTube">
    </a>
  </div>
</footer>

<script src="../js/main.js"></script>
</body>
</html>