<?php
session_start(); // Start the session to use session messages

// Database connection
$conn = new mysqli('localhost', 'root', '', 'login_register');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch products
$sql = "SELECT * FROM products";
$result = $conn->query($sql);

// Check for form submission for adding a product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['product_image'])) {
    $productName = $_POST['product_name'];
    $productPrice = $_POST['product_price'];
    $productImage = $_FILES['product_image'];

    // Handle image upload
    $imageExtension = pathinfo($productImage['name'], PATHINFO_EXTENSION); // Get the file extension (jpg, png, etc.)
    $imageName = time() . '_' . bin2hex(random_bytes(8)) . '.' . $imageExtension; // Generate a unique name
    $targetDir = 'images/';
    $targetFile = $targetDir . $imageName;

    // Check if the file already exists, and if so, regenerate the name
    if (file_exists($targetFile)) {
        $imageName = time() . '_' . bin2hex(random_bytes(8)) . '.' . $imageExtension; // Regenerate a new unique name
        $targetFile = $targetDir . $imageName;
    }

    // Move uploaded image to the target directory
    if (move_uploaded_file($productImage['tmp_name'], $targetFile)) {
        $stmt = $conn->prepare("INSERT INTO products (name, price, image) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $productName, $productPrice, $imageName);
        $stmt->execute();
        $stmt->close();
        
        // Set success message in session
        $_SESSION['message'] = 'Product added successfully!';
        header('Location: dashboard.php?page=admin_dashboard'); // Redirect to reload the page
        exit();
    } else {
        $_SESSION['message'] = 'Image upload failed.';
        header('Location: dashboard.php?page=admin_dashboard');
        exit();
    }
}

// Check for form submission for deleting a product
if (isset($_POST['delete_product'])) {
    $productId = $_POST['product_id'];
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $stmt->close();

    // Set success message in session
    $_SESSION['message'] = 'Product deleted successfully!';
    header('Location: dashboard.php?page=admin_dashboard'); // Redirect to reload the page
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        .alert {
            margin-top: 20px;
        }
        .main {
            margin-left: 0;
            padding: 80px 20px 20px 20px;
            width: 100%;
            transition: margin-left 0.3s ease;
        }
        .product-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: flex-start;
        }
        .product-item {
            background-color: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 200px;
        }
        .product-item img {
            width: 100%;
            height: auto;
            border-radius: 5px;
        }
        .product-item h6 {
            margin: 10px 0 5px;
            font-size: 18px;
        }
        .product-item p {
            color: #555;
        }
        form label {
            margin-top: 10px;
        }
        form input,
        form button {
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
    </style>
</head>
<body>

    <!-- Topbar -->
    <div class="topbar">
        <button class="toggle-btn" onclick="toggleSidebar()">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </button>
    </div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <a href="dashboard.php?page=admin_dashboard">Dashboard</a>
        <a href="dashboard.php?page=manage_users">Manage Users</a>
        <a href="dashboard.php?page=manage_products">Manage Products</a>
        <a href="dashboard.php?page=order">Orders</a>
        <a href="#">Settings</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="main">
    <!-- Display session message in popup -->
    <?php if (isset($_SESSION['message'])): ?>
        <script type="text/javascript">
            alert("<?php echo $_SESSION['message']; ?>");
        </script>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <h2 class="text-center mb-2" style="font-weight:700">Add New Product</h2><hr>
    <form action="dashboard.php?page=admin_dashboard" method="post" enctype="multipart/form-data">
        <div class="container mt-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="product_name" class="form-label">Product Name:</label>
                    <input type="text" name="product_name" id="product_name" class="form-control" placeholder="Enter name..." required>
                </div>
                <div class="col-md-3">
                    <label for="product_price" class="form-label">Product Price:</label>
                    <input type="number" step="0.01" name="product_price" id="product_price" class="form-control" placeholder="Enter price..." required>
                </div>
                <div class="col-md-3">
                    <label for="product_image" class="form-label">Product Image:</label>
                    <input type="file" name="product_image" id="product_image" class="form-control" required>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-plus"></i> Add Product
                    </button>
                </div>
            </div>
        </div>
    </form>
    <hr>
    <br>
    <h2>Product List</h2>
    <div class="product-grid">
        <?php while ($row = $result->fetch_assoc()) { ?>
            <div class="product-item">
                <img src="images/<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>">
                <h6><?php echo $row['name']; ?></h6>
                <p>$<?php echo $row['price']; ?></p>
                <form action="dashboard.php?page=admin_dashboard" method="post">
                    <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                    <button type="submit" name="delete_product" class="btn btn-danger">Delete</button>
                </form>
            </div>
        <?php } ?>
    </div>
</div>

</body>
</html>

<?php $conn->close(); ?>
