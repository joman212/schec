<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name=$_POST["first_name"]; 
    $last_name=$_POST["last_name"]; 
    $email=$_POST["email"];     
$password         = $_POST["password"];
$confirm_password = $_POST["confirm_password"];

if ($password != $confirm_password) {
    echo "Error. Passwords mismatch.<br>";
    die();
}

$password = password_hash($password, PASSWORD_BCRYPT);
    
    $conn= mysqli_connect("localhost","root","","schecter_db");
    if($conn==TRUE) {
    } else {
        echo"Error. Connection failed!<br>"; 
        die();
    }
    
    $stmt="INSERT INTO`users`(`first_name`,`last_name`,`email`,`password`) VALUES('$first_name','$last_name','$email','$password')"; 
    $result= mysqli_query($conn,$stmt); 
    
    if($result==FALSE) {
        echo"Error. Account could not be created.<br>";
    } else {
        echo"Account created successfully!<br>";
        echo"<a href='login.php'>Login here</a>";
        die();
    }
}
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
  
  <div class="oc-logo" style="padding:0 25px 20px;border-bottom:1px solid #333;margin-bottom:15px;">
    <img src="../../images/logo.png" alt="Schecter Guitars" style="height:40px;width:auto;">
  </div>
  
  <a href="../index.html">Home</a>
  <a href="../html/products.html">Guitars</a>
  <a href="../html/Accessories.html">Accessories</a>
  <a href="../html/about.html">About</a>
  <a href="../php/support.php">Support</a>
  <a href="../php/Contact.php">Contact</a>
  <a href="../php/login.php">Sign In</a>
  <a href="../html/cart.html" class="oc-cart">
  <a href="html/cart.html" class="oc-cart">
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
<form action="signup.php" method="post">
    First Name<input type="text" name="first_name" required><br>
    Last Name<input type="text" name="last_name" required><br>
    Email<input type="text" name="email" required><br>
    Password<input type="password" name="password" required><br>
    Confirm Password<input type="password" name="confirm_password" required><br>
    <input type="submit" value="Sign Up">
    <p><a href="login.php">Already have an account?</a></p>
</form>
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