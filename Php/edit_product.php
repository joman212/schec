<?php
session_start();
if (!isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] != TRUE) {
    echo "Error. Access denied. Admin login required.<br>";
    echo "<a href='login.php'>Go to Login</a>";
    die();
}

$conn = mysqli_connect("localhost", "root", "", "schecter_db");
if ($conn == FALSE) { echo "Error. Connection failed!<br>"; die(); }

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id          = $_POST["id"];
    $name        = $_POST["name"];
    $price       = $_POST["price"];
    $description = $_POST["description"];
    $category    = $_POST["category"];
    $stock       = $_POST["stock"];

    // If a new image was uploaded, move it and update the path
    if (!empty($_FILES["image"]["name"])) {
        $img    = $_FILES["image"]["name"];
        $target = __DIR__ . "/../Images/" . $img;
        move_uploaded_file($_FILES["image"]["tmp_name"], $target);
        $db_image_path = "Images/" . $img;
        $stmt = "UPDATE `products` SET `name`='$name', `price`='$price', `description`='$description', `category`='$category', `stock`='$stock', `image`='$db_image_path' WHERE `id`='$id'";
    } else {
        // Keep existing image
        $stmt = "UPDATE `products` SET `name`='$name', `price`='$price', `description`='$description', `category`='$category', `stock`='$stock' WHERE `id`='$id'";
    }

    $result = mysqli_query($conn, $stmt);
    if ($result == FALSE) {
        echo "Error. Product was not updated.<br>";
    } else {
        echo "$name was successfully updated.<br>";
        echo "<a href='admin_dashboard.php' class='btn'>Back to Dashboard</a>";
        die();
    }
}

// Load existing product data
if (!isset($_GET["id"])) { header("Location: admin_dashboard.php"); die(); }
$id     = $_GET["id"];
$stmt   = "SELECT * FROM `products` WHERE `id`='$id'";
$result = mysqli_query($conn, $stmt);
if ($result == FALSE || mysqli_num_rows($result) == 0) {
    echo "Product not found.<br>";
    echo "<a href='admin_dashboard.php'>Back</a>";
    die();
}
$product = mysqli_fetch_assoc($result);

// Handle delete
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete"])) {
    $id = $_POST["id"];

    // Fetch image path then delete the file
    $stmt   = "SELECT `image` FROM `products` WHERE `id`='$id'";
    $result = mysqli_query($conn, $stmt);
    if ($result != FALSE && mysqli_num_rows($result) > 0) {
        $row        = mysqli_fetch_assoc($result);
        $image_path = __DIR__ . "/../" . $row["image"];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }

    $stmt   = "DELETE FROM `products` WHERE `id`='$id'";
    $result = mysqli_query($conn, $stmt);
    if ($result == FALSE) {
        echo "Error. Product was not deleted.<br>";
    } else {
        echo "Product successfully deleted.<br>";
        echo "<a href='admin_dashboard.php' class='btn'>Back to Dashboard</a>";
    }
    die();
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
<div class="page-wrapper">
    <h1 class="page-title">Edit Product</h1>
    <div class="form-container">
        <form action="edit_product.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
            <div class="form-group">
                <label>Product Name</label>
                <input type="text" name="name" value="<?php echo $product['name']; ?>" required>
            </div>
            <div class="form-group">
                <label>Price</label>
                <input type="text" name="price" value="<?php echo $product['price']; ?>" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" required><?php echo $product['description']; ?></textarea>
            </div>
            <div class="form-group">
                <label>Category</label>
                <input type="text" name="category" value="<?php echo $product['category']; ?>" required>
            </div>
            <div class="form-group">
                <label>Stock</label>
                <input type="number" name="stock" value="<?php echo $product['stock']; ?>" required>
            </div>
            <div class="form-group">
                <label>Current Image</label><br>
                <img src="../<?php echo $product['image']; ?>" alt="Product Image" style="height:80px; margin-bottom:8px;"><br>
                <label>Replace Image (optional)</label>
                <input type="file" name="image">
            </div>
            <input type="submit" value="Update Product">
            <div class="form-container" style="margin-top:20px;">
    <form action="edit_product.php" method="post" onsubmit="return confirm('Are you sure you want to delete this product? This cannot be undone.')">
        <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
        <input type="hidden" name="delete" value="1">
        <input type="submit" value="Delete Product" style="background-color:red;">
    </form>
</div>
        </form>
</div>
    </div>
    <div class="form-links">
        <a href="admin_dashboard.php">Back to Dashboard</a>
    </div>
</div>
</body>
</html>