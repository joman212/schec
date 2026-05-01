<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "schecter_db");
if (!$conn) { die("DB connection failed"); }

$logged_in = isset($_SESSION['user_id']);
$user_id = $logged_in ? (int)$_SESSION['user_id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && 
    !empty($_SERVER['CONTENT_TYPE']) && 
    strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    
    header('Content-Type: application/json');
    
    if (!$logged_in) {
        echo json_encode(['success' => false, 'message' => 'not_logged_in']);
        exit;
    }
    
    $rawInput = file_get_contents('php://input');
    $data = $rawInput ? json_decode($rawInput, true) : [];
    $action = $data['action'] ?? '';
    $product_id = isset($data['product_id']) ? (int)$data['product_id'] : 0;
    
    if ($action === 'fetch') {
        $result = mysqli_query($conn, 
            "SELECT c.quantity, p.id, p.name, p.price, p.image 
             FROM cart c 
             JOIN products p ON c.product_id = p.id 
             WHERE c.user_id = $user_id"
        );
        $items = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = [
                'id' => (string)$row['id'],
                'name' => $row['name'],
                'price' => (float)$row['price'],
                'image' => $row['image'],
                'quantity' => (int)$row['quantity']
            ];
        }
        echo json_encode(['success' => true, 'items' => $items]);
        exit;
    }
    
    if ($action === 'remove' && $product_id > 0) {
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param('ii', $user_id, $product_id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => true]);
        exit;
    }
    
    if ($action === 'update' && $product_id > 0 && isset($data['quantity'])) {
        $quantity = max(1, (int)$data['quantity']);
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param('iii', $quantity, $user_id, $product_id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => true]);
        exit;
    }
    
    echo json_encode(['success' => false, 'message' => 'Unknown action']);
    exit;
}

$result = mysqli_query($conn, 
    "SELECT c.quantity, p.id, p.name, p.price, p.image 
     FROM cart c 
     JOIN products p ON c.product_id = p.id 
     WHERE c.user_id = $user_id"
);
$items = [];
while ($row = mysqli_fetch_assoc($result)) {
    $items[] = [
        'id' => (string)$row['id'],
        'name' => $row['name'],
        'price' => (float)$row['price'],
        'image' => $row['image'],
        'quantity' => (int)$row['quantity']
    ];
}

echo '<script>localStorage.setItem("userCart", ' . json_encode($items) . ');</script>';

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
content="width=device-width, initial-scale=1.0">
    <title>Schecter Guitars</title>
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
  <a href="../html/products.html">Guitars</a>
  <a href="../html/Accessories.html">Accessories</a>
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
        <div class="logo">
      <img src="../images/logo.png" alt="Schecter Guitars">
    </div>
    <span style="font-size:30px;cursor:pointer;color:#fff;margin-right:15px;" onclick="openNav()">&#9776;</span>
  </header>

  <main class="cart-wrapper">
    <h1 class="cart-title">Shopping Cart</h1>
    <div id="cartContainer"><p style="color:#E5E5E5;text-align:center;padding:3rem">Loading cart...</p></div>
    <div id="cartSummary" class="cart-summary" style="display:none">
      <div class="cart-total">Total: $<span id="cartTotal">0.00</span></div>
      <a href="../php/Checkout.php" class="checkout-btn">Proceed to Checkout</a>
    </div>
  </main>

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