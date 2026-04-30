<?php
// ── Handle Add to Cart POST (called via fetch from this same page) ────────────
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'not_logged_in']);
        exit;
    }

    $data       = json_decode(file_get_contents('php://input'), true);
    $product_id = isset($data['product_id']) ? (int)$data['product_id'] : 0;
    $quantity   = isset($data['quantity'])   ? (int)$data['quantity']   : 1;

    if ($product_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product']);
        exit;
    }

    $conn = new mysqli('localhost', 'root', '', 'schecter_db');
    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'message' => 'DB error']);
        exit;
    }

    $user_id = (int)$_SESSION['user_id'];

    $stmt = $conn->prepare(
        "INSERT INTO cart (user_id, product_id, quantity)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)"
    );
    $stmt->bind_param('iii', $user_id, $product_id, $quantity);

    if ($stmt->execute()) {
        $count_stmt = $conn->prepare(
            "SELECT SUM(quantity) AS total FROM cart WHERE user_id = ?"
        );
        $count_stmt->bind_param('i', $user_id);
        $count_stmt->execute();
        $total = (int)($count_stmt->get_result()->fetch_assoc()['total'] ?? 0);
        echo json_encode(['success' => true, 'cart_count' => $total]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Insert failed']);
    }

    $stmt->close();
    $conn->close();
    exit;
}
// ─────────────────────────────────────────────────────────────────────────────
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schecter Guitars</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="icon" href="../images/icon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        #cart-toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #1a1a1a;
            color: #fff;
            border-left: 4px solid #D4AF37;
            padding: 14px 22px;
            border-radius: 4px;
            font-family: 'Noto Sans', sans-serif;
            font-size: 14px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.5);
            opacity: 0;
            transform: translateY(10px);
            transition: opacity 0.3s ease, transform 0.3s ease;
            z-index: 9999;
            pointer-events: none;
        }
        #cart-toast.show      { opacity: 1; transform: translateY(0); }
        #cart-toast.error     { border-left-color: #e74c3c; }
        .add-to-cart.loading  { opacity: 0.7; cursor: not-allowed; pointer-events: none; }
    </style>
</head>
<body>

<div id="myOffcanvasNav" class="oc-sidebar">
    <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
    <div class="oc-logo" style="padding:0 25px 20px;border-bottom:1px solid #333;margin-bottom:15px;">
        <img src="../images/logo.png" alt="Schecter Guitars" style="height:40px;width:auto;">
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

<section class="product-details">
    <div class="image-gallery">
        <input type="radio" id="img21" name="gallery" checked>
        <input type="radio" id="img22" name="gallery">
        <input type="radio" id="img23" name="gallery">
        <input type="radio" id="img24" name="gallery">

        <div class="main-image">
            <img src="../images/Synyster-standard.avif" alt="Synyster Standard">
        </div>
        <div class="thumbnails">
            <label for="img21"><img src="../images/synyster-standard.avif"  alt="Synyster"></label>
            <label for="img22"><img src="../images/synyster-standard2.avif" alt="Front View"></label>
            <label for="img23"><img src="../images/synyster-standard3.avif" alt="Side View"></label>
            <label for="img24"><img src="../images/synyster-standard4.avif" alt="Back View"></label>
        </div>
    </div>

    <div class="details">
        <h1 class="itemName">Synyster Gates Standard</h1>
        <h4>Gloss Black with Silver Pinstripes</h4>
        <p>A high-performance guitar designed for rock and metal players.</p>
        <p class="price">$<span class="itemPrice">949</span>.00</p>
        <h3>Specifications:</h3>
        <ul>
            <li>Mahogany body with Gloss Black finish</li>
            <li>Duncan Designed HB-108 Detonator pickups</li>
            <li>Floyd Rose Special bridge</li>
            <li>Rosewood fingerboard with pearloid inlays</li>
            <li>24 Extra Jumbo Frets</li>
        </ul>
        <section class="gates">
            <img src="../images/gates.avif" alt="Synyster Gates Signature">
        </section>

        <button class="add-to-cart btn"
                data-id="Synyster-standard" 
                data-product-id="2"
                data-name="Synyster Standard"
                data-price="949.00"
                data-image="../images/Synyster-standard.avif">
            Add to Cart
        </button>
    </div>
</section>

<section class="reviews">
    <h2>Customer Reviews</h2>
    <div class="review">
        <p style="color:#D4AF37;margin-bottom:5px;">★★★★★</p>
        <p><strong>John D.</strong> - "Amazing tone and quality! The best acoustic I've ever owned."</p>
    </div>
    <div class="review">
        <p style="color:#D4AF37;margin-bottom:5px;">★★★★★</p>
        <p><strong>Lisa M.</strong> - "Great sound, and the Fishman preamp makes it even better!"</p>
    </div>
    <div class="review">
        <p style="color:#D4AF37;margin-bottom:5px;">★★★★☆</p>
        <p><strong>Alex R.</strong> - "Beautiful finish and comfortable to play."</p>
    </div>
</section>

<footer>
    <p>&copy; 2026 Schecter Guitars. All rights reserved.</p>
    <div class="social-media-icons">
        <a href="https://www.facebook.com/SchecterGuitarResearch" target="_blank"><img src="../images/fb.gif" alt="Facebook"></a>
        <a href="https://twitter.com/SchecterGuitars"              target="_blank"><img src="../images/twitter.png" alt="Twitter"></a>
        <a href="https://www.instagram.com/schecterguitarsofficial/" target="_blank"><img src="../images/instagram.png" alt="Instagram"></a>
        <a href="https://www.youtube.com/user/SchecterGuitars"      target="_blank"><img src="../images/youtube.png" alt="YouTube"></a>
    </div>
</footer>

<div id="cart-toast"></div>

<script src="../js/main.js"></script>
<script>
(function () {
    function showToast(msg, isError) {
        var t = document.getElementById('cart-toast');
        t.textContent = msg;
        t.className   = 'show' + (isError ? ' error' : '');
        clearTimeout(t._timer);
        t._timer = setTimeout(function () { t.className = ''; }, 3000);
    }

    function updateBadge(n) {
        document.querySelectorAll('.cart-count').forEach(function (el) {
            el.textContent = n;
        });
    }

    // localStorage fallback for guests
    function localAdd(btn) {
        var cart  = JSON.parse(localStorage.getItem('schecter_cart') || '[]');
        var pid   = btn.dataset.productId;
        var found = cart.find(function (i) { return i.product_id === pid; });
        if (found) {
            found.quantity += 1;
        } else {
            cart.push({ product_id: pid, name: btn.dataset.name,
                        price: parseFloat(btn.dataset.price),
                        image: btn.dataset.image, quantity: 1 });
        }
        localStorage.setItem('schecter_cart', JSON.stringify(cart));
        updateBadge(cart.reduce(function (s, i) { return s + i.quantity; }, 0));
        showToast('Added to cart — sign in to save it', false);
    }

    document.querySelectorAll('.add-to-cart').forEach(function (btn) {
        btn.addEventListener('click', function () {
            btn.classList.add('loading');

            // POST to THIS same page — PHP exits early and returns JSON
            fetch(window.location.href, {
                method : 'POST',
                headers: { 'Content-Type': 'application/json' },
                body   : JSON.stringify({
                    product_id: parseInt(btn.dataset.productId),
                    quantity  : 1
                })
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    updateBadge(data.cart_count);
                    showToast('Added to cart!', false);
                } else if (data.message === 'not_logged_in') {
                    localAdd(btn);
                } else {
                    showToast('Could not add to cart. Try again.', true);
                }
            })
            .catch(function () { localAdd(btn); })
            .finally(function () { btn.classList.remove('loading'); });
        });
    });

    // Init badge for guest
    (function () {
        var cart  = JSON.parse(localStorage.getItem('schecter_cart') || '[]');
        var total = cart.reduce(function (s, i) { return s + i.quantity; }, 0);
        if (total > 0) updateBadge(total);
    })();
})();
</script>

</body>
</html>