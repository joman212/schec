<?php
session_start(); // ← must be before any output

if(!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$conn = mysqli_connect("localhost","root","","schecter_db");
if(!$conn) { echo "Connection failed!"; die(); }

$user_id = $_SESSION["user_id"];
$msg = "";

// ── ADD TO CART (posted from product pages) ──────────────────────────
if(isset($_POST["action"]) && $_POST["action"] == "add") {
    $product_id = $_POST["product_id"];
    $quantity   = intval($_POST["quantity"]);
    if($quantity < 1) $quantity = 1;

    // If already in cart, increase quantity; otherwise insert
    $check = "SELECT * FROM `cart` WHERE `user_id`='$user_id' AND `product_id`='$product_id'";
    $check_result = mysqli_query($conn, $check);

    if(mysqli_num_rows($check_result) > 0) {
        $stmt = "UPDATE `cart` SET `quantity`=`quantity`+'$quantity'
                 WHERE `user_id`='$user_id' AND `product_id`='$product_id'";
    } else {
        $stmt = "INSERT INTO `cart` (`user_id`,`product_id`,`quantity`)
                 VALUES ('$user_id','$product_id','$quantity')";
    }
    mysqli_query($conn, $stmt);
    header("Location: cart.php");
    exit();
}

// ── REMOVE FROM CART ─────────────────────────────────────────────────
if(isset($_GET["remove_id"])) {
    $cart_id = $_GET["remove_id"];
    $stmt = "DELETE FROM `cart` WHERE `id`='$cart_id' AND `user_id`='$user_id'";
    mysqli_query($conn, $stmt);
    $msg = "Item removed.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart - Schecter</title>
    <link rel="stylesheet" href="../css/style.css">  <!-- ← fixed path -->
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
  <a href="support.php">Support</a>
  <a href="Contact.php">Contact</a>
  <a href="account.php">My Account</a>
  <a href="login.php?logout=1">Sign Out</a>
  <a href="cart.php" class="oc-cart">
    <img src="../images/cart.png" alt="Cart" style="width:20px;vertical-align:middle;margin-right:8px;">
    Cart
  </a>
</div>

<header>
  <div class="logo"><img src="../images/logo.png" alt="Schecter Guitars"></div>
  <span style="font-size:30px;cursor:pointer;color:#fff;margin-right:15px;" onclick="openNav()">&#9776;</span>
</header>

<div class="page-wrapper">
    <h1 class="page-title">My Cart</h1>
    <p>Welcome, <?php echo $_SESSION["user_name"]; ?>!</p>
    <div class="form-links">
        <a href="../html/products.html">Continue Shopping</a> |
        <a href="account.php">My Account</a> |
        <a href="login.php?logout=1">Logout</a>
    </div>

    <?php if($msg): ?>
        <p style="color:green;"><?php echo $msg; ?></p>
    <?php endif; ?>

    <hr>
    <h3>Your Items</h3>

    <?php
    $stmt = "SELECT c.id, c.quantity, p.name, p.price, p.image
             FROM `cart` c
             INNER JOIN `products` p ON c.product_id = p.id
             WHERE c.user_id = '$user_id'";
    $result = mysqli_query($conn, $stmt);

    if($result && mysqli_num_rows($result) > 0):
        $total = 0;
    ?>
    <table border="1" style="width:100%;border-collapse:collapse;">
        <tr>
            <th>Product</th><th>Unit Price</th><th>Qty</th>
            <th>Subtotal</th><th>Action</th>
        </tr>
        <?php while($row = mysqli_fetch_assoc($result)):
            $subtotal = $row["price"] * $row["quantity"];
            $total += $subtotal;
        ?>
        <tr>
            <td>
                <img src="../<?php echo $row['image']; ?>"
                     style="width:50px;height:50px;object-fit:cover;vertical-align:middle;">
                <?php echo $row["name"]; ?>
            </td>
            <td>$<?php echo number_format($row["price"],2); ?></td>
            <td><?php echo $row["quantity"]; ?></td>
            <td>$<?php echo number_format($subtotal,2); ?></td>
            <td>
                <!-- ← remove now correctly points to cart.php -->
                <a href="cart.php?remove_id=<?php echo $row['id']; ?>"
                   onclick="return confirm('Remove this item?')">Remove</a>
            </td>
        </tr>
        <?php endwhile; ?>
        <tr>
            <td colspan="3" style="text-align:right;"><strong>Total:</strong></td>
            <td colspan="2"><strong>$<?php echo number_format($total,2); ?></strong></td>
        </tr>
    </table>
    <br>
    <button onclick="alert('Checkout coming soon!')"
            style="background:#c41e3a;color:white;padding:12px 20px;border:none;border-radius:5px;cursor:pointer;">
        Proceed to Checkout
    </button>

    <?php else: ?>
        <p>Your cart is empty. <a href="../html/products.html">Start shopping</a></p>
    <?php endif; ?>
</div>

<footer>
    <p>&copy; 2026 Schecter Guitars. All rights reserved.</p>
</footer>
<script src="../js/main.js"></script>
</body>
</html>