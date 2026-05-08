<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "schecter_db") or die("Connection failed!");

if (isset($_GET["logout"])) {
    session_destroy();
    echo '<script>localStorage.removeItem("schecterCurrentUser");window.location.href="../index.html";</script>';
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email    = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if (!$email || !$password) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id, first_name, email, password, is_admin FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if ($user && (password_verify($password, $user["password"]) || $password === $user["password"])) {
            session_regenerate_id(true);
            $_SESSION["user_id"]    = (int)$user["id"];
            $_SESSION["user_email"] = $user["email"];
            $_SESSION["user_name"]  = $user["first_name"];
            $_SESSION["is_admin"]   = (bool)$user["is_admin"];
            $redirect = $_SESSION["is_admin"] ? "admin_dashboard.php" : "../html/account.html";
            echo '<script>
                localStorage.setItem("schecterCurrentUser", JSON.stringify({
                    email: "' . addslashes($user["email"]) . '",
                    name: "' . addslashes($user["first_name"]) . '",
                    id: ' . (int)$user["id"] . '
                }));
                window.dispatchEvent(new Event("storage"));
                window.location.href = "' . $redirect . '";
            </script>';
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    }
}

mysqli_close($conn);
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
        <h1 class="auth-title">Sign In</h1>
        <p class="auth-subtitle">Welcome back</p>
 
        <?php if (!empty($error)): ?>
            <div class="auth-message error" style="display:block;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
 
        <form class="auth-form" action="login.php" method="post">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="you@example.com" required>
            </div>
 
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>
 
            <button type="submit" class="auth-btn">Login</button>
        </form>
 
        <div class="auth-switch">
            Don't have an account? <a href="signup.php">Create one</a>
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