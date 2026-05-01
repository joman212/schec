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
  
  <a href="../html/cart.html" class="oc-cart">
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
if(!isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] != TRUE) {
    echo"Error. Access denied. Admin login required.<br>";
    echo"<a href='login.php'>Go to Login</a>";
    die();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name=$_POST["name"]; 
    $price=$_POST["price"]; 
    $description=$_POST["description"]; 
    $category=$_POST["category"]; 
    $stock=$_POST["stock"]; 
    
    $img=$_FILES["image"]["name"];
    $target="Images/".$img;
    move_uploaded_file($_FILES["image"]["tmp_name"],$target);
    
    $conn= mysqli_connect("localhost","root","","schecter_db");
    if($conn==TRUE) {
    } else {
        echo"Error. Connection failed!<br>";
        die();
    }
    
    $stmt="INSERT INTO`products`(`name`,`price`,`image`,`description`,`category`,`stock`) VALUES('$name','$price','$target','$description','$category','$stock')";
    $result= mysqli_query($conn,$stmt);
    if($result==FALSE) {
        echo"Error. Product was not added.<br>";
    } else {
        echo"$name was successfully added<br>";
        echo"<a href='add_product.php' class='btn'>Add Another</a> | <a href='admin_dashboard.php' class='btn'>Back to Dashboard</a>";
        die();
    }
}
?>
<div class="page-wrapper">
    <h1 class="page-title">Add New Product</h1>
    <div class="form-container">
        <form action="add_product.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label>Product Name</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Price</label>
                <input type="text" name="price" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" required></textarea>
            </div>
            <div class="form-group">
                <label>Category</label>
                <input type="text" name="category" required>
            </div>
            <div class="form-group">
                <label>Stock</label>
                <input type="number" name="stock" required>
            </div>
            <div class="form-group">
                <label>Upload Image</label>
                <input type="file" name="image" required>
            </div>
            <input type="submit" value="Add Product">
        </form>
    </div>
    <div class="form-links">
        <a href="admin_dashboard.php">Back to Dashboard</a>
    </div>
</div>
</body>
</html>