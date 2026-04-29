
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
<?php
session_start();
if(!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$conn = mysqli_connect("localhost","root","","schecter_db");
if($conn == TRUE) {} else { echo "Error. Connection failed!<br>"; die(); }

// ✅ HANDLE REMOVE FROM CART (if remove button clicked)
if(isset($_GET["remove_id"])) {
    $cart_id = $_GET["remove_id"];
    $stmt = "DELETE FROM`cart` WHERE`id`='$cart_id' AND`user_id`='".$_SESSION["user_id"]."'";
    $result = mysqli_query($conn, $stmt);
    $remove_msg = ($result != FALSE) ? "✅ Item removed." : "Error removing item.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart - Schecter</title>
    <link rel="stylesheet" href="Css/style.css">
    <link rel="icon" href="Images/icon.ico">
</head>
<body>

<div class="page-wrapper">
    <h1 class="page-title">My Cart</h1>
    <p>Welcome, <?php echo $_SESSION["user_name"]; ?>!</p>
    
    <div class="form-links">
        <a href="products.php">Continue Shopping</a> | 
        <a href="logout.php">Logout</a>
    </div>
    
    <!-- Remove Message -->
    <?php if(isset($remove_msg)): ?>
        <div class="message-box success"><?php echo $remove_msg; ?></div>
    <?php endif; ?>
    
    <hr>
    <h3>Your Items</h3>
    <?php
    $user_id = $_SESSION["user_id"];
    
    // JOIN cart with products to show details (PDF pattern)
    $stmt = "SELECT c.*, p.name, p.price, p.image FROM`cart` c 
             INNER JOIN`products` p ON c.product_id = p.id 
             WHERE c.user_id = '$user_id'";
    $result = mysqli_query($conn, $stmt);
    
    if($result != FALSE && mysqli_num_rows($result) > 0) {
        echo "<table border='1'><tr><th>Product</th><th>Price</th><th>Quantity</th><th>Subtotal</th><th>Action</th></tr>";
        $total = 0;
        while($row = mysqli_fetch_assoc($result)) {
            $subtotal = $row["price"] * $row["quantity"];
            $total += $subtotal;
            echo "<tr>
                <td><img src='".$row["image"]."' style='width:50px;height:50px;object-fit:cover;'> ".$row["name"]."</td>
                <td>$".$row["price"]."</td>
                <td>".$row["quantity"]."</td>
                <td>$".number_format($subtotal, 2)."</td>
                <td><a href='account.php?remove_id=".$row["id"]."'>Remove</a></td>
            </tr>";
        }
        echo "<tr><td colspan='3' style='text-align:right;'><strong>Total:</strong></td><td colspan='2'><strong>$".number_format($total, 2)."</strong></td></tr>";
        echo "</table>";
        echo "<br><input type='button' value='Proceed to Checkout' onclick=\"alert('Checkout feature coming soon!')\" style='background:#c41e3a;color:white;padding:12px 20px;border:none;border-radius:5px;cursor:pointer;'>";
    } else {
        echo "<p>Your cart is empty. <a href='products.php'>Start shopping</a></p>";
    }
    ?>
</div>

</body>
</html>