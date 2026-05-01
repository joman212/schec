<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "schecter_db");
if (!$conn) { die("Error. Connection failed!<br>"); }

// Handle logout
if (isset($_GET["logout"])) {
    session_destroy();
    // Clear localStorage via JS on logout page
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8">
    <script>
        localStorage.removeItem("schecterCurrentUser");
        sessionStorage.removeItem("schecterCurrentUser");
        window.location.href = "../index.html";
    </script></head><body></body></html>';
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id, first_name, email, password, is_admin FROM users WHERE email = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($result && mysqli_num_rows($result) === 1) {
                $user = mysqli_fetch_assoc($result);

                // Verify password (supports both hashed and plain for dev)
                if (password_verify($password, $user["password"]) || $password === $user["password"]) {
                    session_regenerate_id(true);
                    $_SESSION["user_id"]    = (int)$user["id"];
                    $_SESSION["user_email"] = $user["email"];
                    $_SESSION["user_name"]  = $user["first_name"];
                    $_SESSION["is_admin"]   = (bool)$user["is_admin"];

                    // ✅ KEY FIX: Sync PHP session → localStorage via JS, then redirect
                    // This ensures main.js knows the user is logged in after redirect
                    $redirect = $_SESSION["is_admin"] ? "admin_dashboard.php" : "../html/account.html";
                    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Redirecting...</title></head><body><script>
                    localStorage.setItem("schecterCurrentUser", JSON.stringify({
                        email: "' . addslashes($user["email"]) . '",
                        name: "' . addslashes($user["first_name"]) . '",
                        id: "' . (int)$user["id"] . '"
                    }));
                    // Trigger auth update events for any open tabs
                    window.dispatchEvent(new Event("storage"));
                    window.location.href = "' . $redirect . '";
                    </script></body></html>';
                    exit();
                } else {
                    $error = "Invalid email or password.";
                }
            } else {
                $error = "Invalid email or password.";
            }
            mysqli_stmt_close($stmt);
        } else {
            $error = "Database query failed.";
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
<?php if (!empty($error)): ?>
    <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>
<form action="login.php" method="post">
    Email<input type="text" name="email" required><br>
    Password<input type="password" name="password" required><br>
    <input type="submit" value="Login">
    <p><a href="signup.php">Create Account</a></p>
</form>
<footer>
    <p>&copy; 2026 Schecter Guitars. All rights reserved.</p>
    <div class="social-media-icons">
        <a href="https://www.facebook.com/SchecterGuitarResearch" target="_blank" aria-label="Facebook"><img src="../images/fb.gif" alt="Facebook"></a>
        <a href="https://twitter.com/SchecterGuitars" target="_blank" aria-label="Twitter"><img src="../images/twitter.png" alt="Twitter"></a>
        <a href="https://www.instagram.com/schecterguitarsofficial/" target="_blank" aria-label="Instagram"><img src="../images/instagram.png" alt="Instagram"></a>
        <a href="https://www.youtube.com/user/SchecterGuitars" target="_blank" aria-label="YouTube"><img src="../images/youtube.png" alt="YouTube"></a>
    </div>
</footer>
<script src="../js/main.js"></script>
</body>
</html>