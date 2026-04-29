<?php
session_start();

if(!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$conn = mysqli_connect("localhost","root","","schecter_db");
if(!$conn) { echo "Connection failed!"; die(); }

$user_id = $_SESSION["user_id"];

// Fetch full user info
$stmt   = "SELECT * FROM `users` WHERE `id`='$user_id'";
$result = mysqli_query($conn, $stmt);
$user   = mysqli_fetch_assoc($result);

// Fetch order history
$order_stmt   = "SELECT * FROM `orders` WHERE `user_id`='$user_id' ORDER BY `created_at` DESC";
$order_result = mysqli_query($conn, $order_stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - Schecter</title>
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
  <a href="support.php">Support</a>
  <a href="Contact.php">Contact</a>
  <a href="cart.php">My Cart</a>
  <a href="login.php?logout=1">Sign Out</a>
</div>

<header>
  <div class="logo"><img src="../images/logo.png" alt="Schecter Guitars"></div>
  <span style="font-size:30px;cursor:pointer;color:#fff;margin-right:15px;" onclick="openNav()">&#9776;</span>
</header>

<div class="page-wrapper">
    <h1 class="page-title">My Account</h1>

    <!-- ── Account Info ── -->
    <section>
        <h2>Account Details</h2>
        <p><strong>Name:</strong> <?php echo $user["first_name"]." ".$user["last_name"]; ?></p>
        <p><strong>Email:</strong> <?php echo $user["email"]; ?></p>
        <p><strong>Member Since:</strong> <?php echo date("F j, Y", strtotime($user["created_at"])); ?></p>
        <a href="cart.php"
           style="display:inline-block;margin-top:10px;background:#c41e3a;color:white;
                  padding:10px 18px;border-radius:5px;text-decoration:none;">
            View My Cart
        </a>
        &nbsp;
        <a href="login.php?logout=1"
           style="display:inline-block;margin-top:10px;background:#333;color:white;
                  padding:10px 18px;border-radius:5px;text-decoration:none;">
            Logout
        </a>
    </section>

    <hr>

    <!-- ── Order History ── -->
    <section>
        <h2>Order History</h2>
        <?php if($order_result && mysqli_num_rows($order_result) > 0): ?>
        <table border="1" style="width:100%;border-collapse:collapse;">
            <tr>
                <th>Order #</th><th>Date</th><th>Total</th><th>Status</th><th>Items</th>
            </tr>
            <?php while($order = mysqli_fetch_assoc($order_result)):
                // Fetch items for this order
                $oid       = $order["id"];
                $item_stmt = "SELECT oi.quantity, oi.price, p.name
                              FROM `order_items` oi
                              INNER JOIN `products` p ON oi.product_id = p.id
                              WHERE oi.order_id = '$oid'";
                $item_result = mysqli_query($conn, $item_stmt);

                // Color-code the status badge
                $status_colors = [
                    "completed"  => "green",
                    "pending"    => "orange",
                    "cancelled"  => "red"
                ];
                $color = $status_colors[$order["status"]] ?? "#333";
            ?>
            <tr>
                <td>#<?php echo $order["id"]; ?></td>
                <td><?php echo date("M j, Y", strtotime($order["created_at"])); ?></td>
                <td>$<?php echo number_format($order["total_amount"],2); ?></td>
                <td><span style="color:<?php echo $color; ?>;font-weight:bold;">
                    <?php echo ucfirst($order["status"]); ?>
                </span></td>
                <td>
                    <?php while($item = mysqli_fetch_assoc($item_result)):?>
                        <?php echo $item["name"]; ?>
                        (x<?php echo $item["quantity"]; ?>)
                        @ $<?php echo number_format($item["price"],2); ?><br>
                    <?php endwhile; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
        <?php else: ?>
            <p>No orders yet. <a href="../html/products.html">Start shopping!</a></p>
        <?php endif; ?>
    </section>
</div>

<footer>
    <p>&copy; 2026 Schecter Guitars. All rights reserved.</p>
    <div class="social-media-icons">
        <a href="https://www.facebook.com/SchecterGuitarResearch" target="_blank"><img src="../images/fb.gif" alt="Facebook"></a>
        <a href="https://twitter.com/SchecterGuitars" target="_blank"><img src="../images/twitter.png" alt="Twitter"></a>
        <a href="https://www.instagram.com/schecterguitarsofficial/" target="_blank"><img src="../images/instagram.png" alt="Instagram"></a>
        <a href="https://www.youtube.com/user/SchecterGuitars" target="_blank"><img src="../images/youtube.png" alt="YouTube"></a>
    </div>
</footer>
<script src="../js/main.js"></script>
</body>
</html>