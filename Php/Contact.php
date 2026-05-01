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
  
<div class="sideNav">
    <a href="https://instagram.com/schecterguitarsofficial" id="instagram" target="_blank" rel="noopener">
        <img src="../images/instagram.png" alt="Instagram"><i class="fab fa-instagram"></i><span>Instagram</span>
    </a>
    <a href="https://facebook.com/schecterguitarsofficial" id="facebook" target="_blank" rel="noopener">
        <img src="../images/fb.gif" alt="Facebook"><i class="fab fa-facebook-f"></i><span>Facebook</span>
    </a>
    <a href="https://twitter.com/SchecterGuitars" id="twitter" target="_blank" rel="noopener">
        <img src="../images/twitter.png" alt="Twitter"><i class="fab fa-x-twitter"></i><span>Twitter</span> 
    </a>
    <a href="https://youtube.com/schecterguitarresearch" id="youtube" target="_blank" rel="noopener">
        <img src="../images/youtube.png" alt="YouTube"><i class="fab fa-youtube"></i><span>YouTube</span>
    </a>
</div>
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $subject = $_POST["subject"];
    $message = $_POST["message"];
    
    $conn = mysqli_connect("localhost","root","","schecter_db");
    if($conn==TRUE) {
        $stmt = "INSERT INTO `contact_messages`(`name`,`email`,`subject`,`message`) 
                 VALUES('$name','$email','$subject','$message')";
        $result = mysqli_query($conn,$stmt);
        if($result==FALSE) echo "Error. Message was not saved<br>";
    } else {
        echo "Error. Connection failed!<br>";
    }
    ?>
    <!DOCTYPE html>
    <html>
    <body>
        <h2>Thank You, <?php echo $name; ?>!</h2>
        <p>Your message was received.</p>
        <a href="../index.html">Back to Home</a>
    </body>
    </html>
    <?php
    exit;
}
?>
<section class="contact">
    <h1>Contact</h1>
    <?php
    $conn= mysqli_connect("localhost","root","","schecter_db"); 
if($conn==TRUE) {
} else {
    echo"Error. Connection failed!<br>"; 
    die();
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name=$_POST["name"]; 
    $email=$_POST["email"]; 
    $subject=$_POST["subject"]; 
    $message=$_POST["message"]; 
    
    include 'db_connect.php';
    
    $stmt="INSERT INTO`contact_messages`(`name`,`email`,`subject`,`message`) VALUES('$name','$email','$subject','$message')"; 
    $result= mysqli_query($conn,$stmt); 
    if($result==FALSE) {
        echo"Error. Message was not sent.<br>"; 
    } else {
        echo"Thank you! Your message has been sent successfully.<br>";
        echo"<a href='../index.html'>Return to Home</a>";
        die();
    }
}
?>
<form action="contact.php" method="post">
    Name<input type="text" name="name" required><br>
    Email<input type="text" name="email" required><br>
    Subject<input type="text" name="subject" required><br>
    Message<textarea name="message" required></textarea><br>
    <input type="submit" value="Send Message">
    </form>
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
