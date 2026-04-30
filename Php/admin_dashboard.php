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
<div class="admin-wrapper">
<?php
session_start();
if(!isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] != TRUE) {
    echo"Error. Access denied. Admin login required.<br>";
    echo"<a href='login.php' class='btn'>Go to Login</a>";
    die();
}

$conn= mysqli_connect("localhost","root","","schecter_db");
if($conn==TRUE) {} else { echo"Error. Connection failed!<br>"; die(); }
?>
<h2>Admin Dashboard</h2>
<h3>Welcome, <?php echo $_SESSION["user_name"]; ?>!</h3>
<a href="add_product.php" class="btn">Add New Product</a> <a href="logout.php" class="btn">Logout</a>
<hr>

<h3>Quick Stats</h3>
<?php
$stmt="SELECT COUNT(*) as total FROM`users`";
$result= mysqli_query($conn,$stmt);
if($result!=FALSE) { $row=mysqli_fetch_assoc($result); echo"Total Users: ".$row["total"]."<br>"; }

$stmt="SELECT COUNT(*) as total FROM`orders`";
$result= mysqli_query($conn,$stmt);
if($result!=FALSE) { $row=mysqli_fetch_assoc($result); echo"Total Orders: ".$row["total"]."<br>"; }

$stmt="SELECT COUNT(*) as total FROM`contact_messages`";
$result= mysqli_query($conn,$stmt);
if($result!=FALSE) { $row=mysqli_fetch_assoc($result); echo"Contact Messages: ".$row["total"]."<br>"; }
?>

<h3>View All Contact Messages</h3>
<?php
$stmt="SELECT * FROM`contact_messages`";
$result= mysqli_query($conn,$stmt);
if($result!=FALSE && mysqli_num_rows($result)>0) {
    echo"<table border='1'><tr><th>ID</th><th>Name</th><th>Email</th><th>Subject</th><th>Message</th></tr>";
    while($row=mysqli_fetch_assoc($result)) {
        echo"<tr>
            <td>".$row["id"]."</td>
            <td>".$row["name"]."</td>
            <td>".$row["email"]."</td>
            <td>".$row["subject"]."</td>
            <td>".$row["message"]."</td>
        </tr>";
    }
    echo"</table>";
} else {
    echo"No messages found.";
}
?>
</div>
</body>
</html>