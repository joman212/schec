<?php
session_start();

$conn = mysqli_connect("localhost", "root", "", "schecter_db");
if (!$conn) { die("DB connection failed"); }

// ── Handle all POST actions ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'not_logged_in']);
        exit;
    }

    $data       = json_decode(file_get_contents('php://input'), true);
    $action     = $data['action']     ?? '';
    $product_id = (int)($data['product_id'] ?? 0);
    $user_id    = (int)$_SESSION['user_id'];

    // Fetch cart items for JS rendering
    if ($action === 'fetch') {
        $result = mysqli_query($conn,
            "SELECT c.quantity, p.id, p.name, p.price, p.image
             FROM cart c
             JOIN products p ON c.product_id = p.id
             WHERE c.user_id = $user_id"
        );
        $items = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = $row;
        }
        echo json_encode(['success' => true, 'items' => $items]);
        exit;
    }

    // Update quantity
    if ($action === 'update' && $product_id > 0) {
        $quantity = (int)($data['quantity'] ?? 1);
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param('iii', $quantity, $user_id, $product_id);
        $stmt->execute();
        echo json_encode(['success' => true]);
        exit;
    }

    // Remove item
    if ($action === 'remove' && $product_id > 0) {
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param('ii', $user_id, $product_id);
        $stmt->execute();
        echo json_encode(['success' => true]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Unknown action']);
    exit;
}
// ─────────────────────────────────────────────────────────────────────────────

$logged_in = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schecter Guitars – Cart</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="icon" href="../images/icon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@300;400;700&display=swap" rel="stylesheet">
</head>
<body>

  <div class="oc-logo" style="padding:0 25px 20px;border-bottom:1px solid #333;margin-bottom:15px;">
    <img src="../images/logo.png" alt="Schecter Guitars" style="height:40px;width:auto;">
  </div>
  
  <a href="index.html">Home</a>
  <a href="html/products.html">Guitars</a>
  <a href="html/Accessories.html">Accessories</a>
  <a href="html/about.html">About</a>
  <a href="php/support.php">Support</a>
  <a href="php/Contact.php">Contact</a>
  <a href="php/login.php">Sign In</a>
  <a href="html/cart.html" class="oc-cart">
    <img src="images/cart.png" alt="Cart" style="width:20px;vertical-align:middle;margin-right:8px;">
    Cart <span class="cart-count">0</span>
  </a>
</div>

<header>
    <div class="logo">
        <img src="../images/logo.png" alt="Schecter Guitars">
    </div>
    <span style="font-size:30px;cursor:pointer;color:#fff;margin-right:15px;" onclick="openNav()">&#9776;</span>
</header>

<main class="cart-wrapper">
    <h1 class="cart-title">Shopping Cart</h1>
    <div id="cartContainer" data-db="true"><p style="color:#E5E5E5;text-align:center;padding:3rem">Loading cart...</p></div>
    <div id="cartSummary" class="cart-summary" style="display:none;">
        <div class="cart-total">Total: $<span id="cartTotal">0.00</span></div>
        <a href="../html/Checkout.html" class="checkout-btn">Proceed to Checkout</a>
    </div>
</main>

<footer>
    <p>&copy; 2026 Schecter Guitars. All rights reserved.</p>
    <div class="social-media-icons">
        <a href="https://www.facebook.com/SchecterGuitarResearch" target="_blank"><img src="../images/fb.gif" alt="Facebook"></a>
        <a href="https://twitter.com/SchecterGuitars"              target="_blank"><img src="../images/twitter.png" alt="Twitter"></a>
        <a href="https://www.instagram.com/schecterguitarsofficial/" target="_blank"><img src="../images/instagram.png" alt="Instagram"></a>
        <a href="https://www.youtube.com/user/SchecterGuitars"      target="_blank"><img src="../images/youtube.png" alt="YouTube"></a>
    </div>
</footer>

<script src="../js/main.js"></script>
<script>
(function () {
    var loggedIn  = <?= $logged_in ? 'true' : 'false' ?>;
    var container = document.getElementById('cartContainer');
    var summary   = document.getElementById('cartSummary');
    var totalEl   = document.getElementById('cartTotal');

    function updateBadge(n) {
        document.querySelectorAll('.cart-count').forEach(function (el) {
            el.textContent = n;
        });
    }

    function post(payload, callback) {
        fetch(window.location.href, {
            method : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body   : JSON.stringify(payload)
        })
        .then(function (r) { return r.json(); })
        .then(callback);
    }

    function attachTableListeners() {
        document.querySelectorAll('.qty-input').forEach(function (input) {
            input.addEventListener('change', function () {
                var qty = parseInt(this.value);
                if (qty < 1) { this.value = 1; qty = 1; }
                post(
                    { action: 'update', product_id: parseInt(this.dataset.productId), quantity: qty },
                    function (data) { if (data.success) loadCart(); }
                );
            });
        });

        document.querySelectorAll('.remove-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                post(
                    { action: 'remove', product_id: parseInt(this.dataset.productId) },
                    function (data) { if (data.success) loadCart(); }
                );
            });
        });
    }

    function render(items) {
        if (!items.length) {
            container.innerHTML = '<p style="color:#E5E5E5;text-align:center;padding:3rem">Your cart is empty. <a href="products.html" style="color:#E76E24">Continue shopping</a></p>';
            summary.style.display = 'none';
            updateBadge(0);
            return;
        }

        var total    = 0;
        var totalQty = 0;

        var rows = items.map(function (item) {
            var subtotal = parseFloat(item.price) * parseInt(item.quantity);
            total    += subtotal;
            totalQty += parseInt(item.quantity);

            return '<tr style="border-bottom:1px solid #2a2a2a;">' +
                '<td style="padding:12px;display:flex;align-items:center;gap:14px;">' +
                    '<img src="../' + item.image + '" alt="' + item.name + '" style="width:70px;height:70px;object-fit:cover;border-radius:4px;" onerror="this.src=\'https://via.placeholder.com/70\'">' +
                    '<span>' + item.name + '</span>' +
                '</td>' +
                '<td style="padding:12px;">$' + parseFloat(item.price).toFixed(2) + '</td>' +
                '<td style="padding:12px;">' +
                    '<input type="number" min="1" value="' + item.quantity + '" class="qty-input" data-product-id="' + item.id + '" style="width:55px;background:#1a1a1a;color:#fff;border:1px solid #444;padding:4px 6px;border-radius:3px;">' +
                '</td>' +
                '<td style="padding:12px;">$' + subtotal.toFixed(2) + '</td>' +
                '<td style="padding:12px;">' +
                    '<button class="remove-btn" data-product-id="' + item.id + '" style="background:none;border:none;color:#e74c3c;cursor:pointer;font-size:18px;" title="Remove">&#10005;</button>' +
                '</td>' +
            '</tr>';
        }).join('');

        container.innerHTML =
            '<table style="width:100%;border-collapse:collapse;color:#E5E5E5;">' +
                '<thead><tr style="border-bottom:1px solid #444;text-align:left;">' +
                    '<th style="padding:12px;">Product</th>' +
                    '<th style="padding:12px;">Price</th>' +
                    '<th style="padding:12px;">Qty</th>' +
                    '<th style="padding:12px;">Subtotal</th>' +
                    '<th style="padding:12px;"></th>' +
                '</tr></thead>' +
                '<tbody>' + rows + '</tbody>' +
            '</table>';

        totalEl.textContent = total.toFixed(2);
        summary.style.display = 'block';
        updateBadge(totalQty);
        attachTableListeners();
    }

    function loadCart() {
        post({ action: 'fetch' }, function (data) {
            if (data.success) render(data.items);
        });
    }

    // ── Init ──────────────────────────────────────────────────────────────────
    if (!loggedIn) {
        container.innerHTML = '<p style="color:#E5E5E5;text-align:center;padding:3rem">Please <a href="../php/login.php" style="color:#E76E24">sign in</a> to view your cart.</p>';
        summary.style.display = 'none';
    } else {
        loadCart();
    }
})();
</script>

</body>
</html>