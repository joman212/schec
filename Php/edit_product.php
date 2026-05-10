<?php
session_start();
if (!isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] != TRUE) {
    echo "Error. Access denied. Admin login required.<br>";
    echo "<a href='login.php'>Go to Login</a>";
    die();
}

$conn = mysqli_connect("localhost", "root", "", "schecter_db");
if ($conn == FALSE) { echo "Error. Connection failed!<br>"; die(); }

$modal_message = "";
$modal_type    = "";
$modal_buttons = [];
$product       = null;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete"])) {
    $id   = $_POST["id"];
    $stmt = "SELECT `image` FROM `products` WHERE `id`='$id'";
    $res  = mysqli_query($conn, $stmt);
    if ($res && mysqli_num_rows($res) > 0) {
        $row        = mysqli_fetch_assoc($res);
        $image_path = __DIR__ . "/../" . $row["image"];
        if (file_exists($image_path)) unlink($image_path);
    }
    $stmt   = "DELETE FROM `products` WHERE `id`='$id'";
    $result = mysqli_query($conn, $stmt);
    $msg    = $result ? "Product successfully deleted." : "Error. Product was not deleted.";
    $type   = $result ? "success" : "error";
    header("Location: admin_dashboard.php?msg=" . urlencode($msg) . "&type=" . $type);
    die();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id          = $_POST["id"];
    $name        = $_POST["name"];
    $price       = $_POST["price"];
    $description = $_POST["description"];
    $category    = $_POST["category"];
    $stock       = $_POST["stock"];

    if (!empty($_FILES["image"]["name"])) {
        $img           = $_FILES["image"]["name"];
        $target        = __DIR__ . "/../Images/" . $img;
        move_uploaded_file($_FILES["image"]["tmp_name"], $target);
        $db_image_path = "Images/" . $img;
        $stmt = "UPDATE `products` SET `name`='$name', `price`='$price', `description`='$description', `category`='$category', `stock`='$stock', `image`='$db_image_path' WHERE `id`='$id'";
    } else {
        $stmt = "UPDATE `products` SET `name`='$name', `price`='$price', `description`='$description', `category`='$category', `stock`='$stock' WHERE `id`='$id'";
    }

    $result = mysqli_query($conn, $stmt);
    if ($result == FALSE) {
        $modal_message = "Error. Product was not updated.";
        $modal_type    = "error";
        $modal_buttons = [["label" => "Try Again", "close" => true, "color" => "#c41e3a"]];
    } else {
        $modal_message = "$name was successfully updated.";
        $modal_type    = "success";
        $modal_buttons = [["label" => "Back to Dashboard", "href" => "admin_dashboard.php", "color" => "#1b5e20"]];
    }

    $stmt    = "SELECT * FROM `products` WHERE `id`='$id'";
    $res     = mysqli_query($conn, $stmt);
    $product = $res ? mysqli_fetch_assoc($res) : null;
}

// Load product on GET
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (!isset($_GET["id"])) { header("Location: admin_dashboard.php"); die(); }
    $id      = $_GET["id"];
    $stmt    = "SELECT * FROM `products` WHERE `id`='$id'";
    $result  = mysqli_query($conn, $stmt);
    if ($result == FALSE || mysqli_num_rows($result) == 0) {
        echo "Product not found.<br><a href='admin_dashboard.php'>Back</a>";
        die();
    }
    $product = mysqli_fetch_assoc($result);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Schecter Guitars</title>
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
  <div class="logo"><img src="../images/logo.png" alt="Schecter Guitars"></div>
  <span style="font-size:30px;cursor:pointer;color:#fff;margin-right:15px;" onclick="openNav()">&#9776;</span>
</header>

<div class="page-wrapper">
    <h1 class="page-title">Edit Product</h1>
    <?php if ($product): ?>
    <div class="form-container">
        <form action="edit_product.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
            <div class="form-group">
                <label>Product Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
            </div>
            <div class="form-group">
                <label>Price</label>
                <input type="text" name="price" value="<?php echo $product['price']; ?>" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" required><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>
            <div class="form-group">
                <label>Category</label>
                <input type="text" name="category" value="<?php echo htmlspecialchars($product['category']); ?>" required>
            </div>
            <div class="form-group">
                <label>Stock</label>
                <input type="number" name="stock" value="<?php echo $product['stock']; ?>" required>
            </div>
            <div class="form-group">
                <label>Current Image</label><br>
                <img src="../<?php echo $product['image']; ?>" alt="Product Image" style="height:80px;margin-bottom:8px;"><br>
                <label>Replace Image (optional)</label>
                <input type="file" name="image">
            </div>
            <input type="submit" value="Update Product">
        </form>

        <form id="deleteForm" action="edit_product.php" method="post">
            <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
            <input type="hidden" name="delete" value="1">
            <input type="submit" id="deleteBtn" value="Delete Product" style="background-color:#c41e3a;">
        </form>
    </div>
    <?php endif; ?>
    <div class="form-links">
        <a href="admin_dashboard.php">Back to Dashboard</a>
    </div>
</div>

<div id="deleteModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.7);z-index:9999;justify-content:center;align-items:center;">
    <div style="background:#1a1a1a;border:2px solid #c41e3a;border-radius:10px;padding:30px;max-width:400px;width:90%;text-align:center;">
        <h3 style="color:#c41e3a;margin-bottom:15px;">Delete Product?</h3>
        <p style="color:#ccc;margin-bottom:25px;">This cannot be undone. The product and its image will be permanently removed.</p>
        <div style="display:flex;gap:15px;justify-content:center;">
            <button id="cancelDelete" style="padding:10px 25px;background:#444;color:#fff;border:none;border-radius:5px;cursor:pointer;font-size:15px;">Cancel</button>
            <button id="confirmDelete" style="padding:10px 25px;background:#c41e3a;color:#fff;border:none;border-radius:5px;cursor:pointer;font-size:15px;">Delete</button>
        </div>
    </div>
</div>

<?php if (!empty($modal_message)): ?>
<script>
window._adminMsg = {
    message: <?php echo json_encode($modal_message); ?>,
    type:    <?php echo json_encode($modal_type); ?>,
    buttons: <?php echo json_encode($modal_buttons); ?>
};
</script>
<?php endif; ?>
<script src="../Js/main.js"></script>
</body>
</html>