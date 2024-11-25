<?php
$conn = new mysqli('localhost', 'root', '', 'login_register');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check for form submission for deleting a product
if (isset($_POST['delete_product'])) {
    $productId = $_POST['product_id'];
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $stmt->close();
}

// Check for form submission for editing a product
if (isset($_POST['edit_product'])) {
    $productId = $_POST['product_id'];
    $productName = $_POST['product_name'];
    $productPrice = $_POST['product_price'];
    $productImage = $_FILES['product_image'];

    // Handle image upload if new image is selected
    if ($productImage['name'] != '') {
        $imageExtension = pathinfo($productImage['name'], PATHINFO_EXTENSION); // Get the file extension
        $imageName = time() . '_' . bin2hex(random_bytes(8)) . '.' . $imageExtension; // Generate a unique name
        $targetDir = 'images/';
        $targetFile = $targetDir . $imageName;

        if (move_uploaded_file($productImage['tmp_name'], $targetFile)) {
            // Update product details in database including new image
            $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, image = ? WHERE id = ?");
            $stmt->bind_param("sssi", $productName, $productPrice, $imageName, $productId);
        } else {
            echo "Image upload failed.";
        }
    } else {
        // If no new image is uploaded, update without changing the image
        $stmt = $conn->prepare("UPDATE products SET name = ?, price = ? WHERE id = ?");
        $stmt->bind_param("ssi", $productName, $productPrice, $productId);
    }

    $stmt->execute();
    $stmt->close();
}

// Fetch all products
$sql = "SELECT * FROM products";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <div class="sidebar" id="sidebar">
        <a href="dashboard.php?page=admin_dashboard">Dashboard</a> 
        <a href="dashboard.php?page=manage_users">Manage Users</a>
        <a href="dashboard.php?page=manage_products">Manage Products</a>
        <a href="dashboard.php?page=order">Orders</a> <!-- Correct link to orders management -->
        <a href="#">Settings</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="topbar">
        <button class="toggle-btn" onclick="toggleSidebar()">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </button>
    </div>

    <!-- Main Content -->
    <div class="main">
        <div class="container mb-3">
            <h2 class="text-center mb-4">Manage Products</h2>
            <table id="productTable" class="table table-hover table-striped text-center">
                <thead class="table-dark">
                    <tr>
                        <th>Id</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Image</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $serialNumber = 1; // Initialize a counter for the serial number
                    while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $serialNumber++; ?></td>
                            <td><?php echo $row['name']; ?></td>
                            <td>$<?php echo $row['price']; ?></td>
                            <td><img src="images/<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>" class="img-fluid" width="100"></td>
                            <td>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $row['id']; ?>"><i class="fas fa-edit"></i> Edit</button>
                                <form action="" method="post" style="display:inline-block;">
                                    <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="delete_product" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this product?');"><i class="fas fa-trash"></i> Delete</button>
                                </form>
                            </td>
                        </tr>

                        <!-- Edit Product Modal -->
                        <div class="modal fade" id="editModal<?php echo $row['id']; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Product</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="" method="post" enctype="multipart/form-data">
                                            <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                            <div class="mb-3">
                                                <label class="form-label">Product Name</label>
                                                <input type="text" name="product_name" class="form-control" value="<?php echo $row['name']; ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Product Price</label>
                                                <input type="number" name="product_price" class="form-control" value="<?php echo $row['price']; ?>" step="0.01" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Product Image</label>
                                                <input type="file" name="product_image" class="form-control">
                                                <small class="form-text text-muted">Leave empty to keep the current image.</small>
                                            </div>
                                            <button type="submit" name="edit_product" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- JavaScript to Toggle Sidebar -->
    <script>
        function toggleSidebar() {
            var sidebar = document.getElementById("sidebar");
            sidebar.classList.toggle("open");

            var main = document.querySelector(".main");
            if (sidebar.classList.contains("open")) {
                main.style.marginLeft = "200px";
            } else {
                main.style.marginLeft = "0";
            }
        }
    </script>

    <!-- DataTables and Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#productTable').DataTable({
                responsive: true
            });
        });
    </script>

</body>
</html>
