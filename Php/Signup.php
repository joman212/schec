<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $last_name  = $_POST['last_name'];
    $email      = $_POST['email'];
    $password   = $_POST['password'];

    if ($password !== $_POST['confirm_password']) die("Error. Passwords do not match.");

    $password = password_hash($password, PASSWORD_BCRYPT);

    $conn = new mysqli('localhost', 'root', '', 'schecter_db');
    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('ssss', $first_name, $last_name, $email, $password);

    if ($stmt->execute()) {
        echo "Account created! <a href='login.php'>Login here</a>";
    } else {
        echo "Error. Account could not be created.";
    }
    exit;
}
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
</head>
<body>
 
<div id="myOffcanvasNav" class="oc-sidebar">
    <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
    <div class="oc-logo">
        <img src="../images/logo.png" alt="Schecter Guitars">
    </div>
    <a href="../index.html">Home</a>
    <a href="../php/products.php">Guitars</a>
    <a href="../php/accessories.php">Accessories</a>
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
 
<div class="auth-wrapper">
    <div class="auth-card">
        <h1 class="auth-title">Create Account</h1>
        <p class="auth-subtitle">Join the Schecter community</p>
 
        <form class="auth-form" action="signup.php" method="post">
            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" placeholder="John" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" placeholder="Doe" required>
                </div>
            </div>
 
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="you@example.com" required>
            </div>
 
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>
 
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="••••••••" required>
            </div>
 
            <button type="submit" class="auth-btn">Sign Up</button>
        </form>
 
        <div class="auth-switch">
            Already have an account? <a href="login.php">Sign in</a>
        </div>
    </div>
</div>
 
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