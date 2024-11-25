<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'login_register');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Update order status
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];

    $update_sql = "UPDATE orders SET status = '$status' WHERE id = $order_id";
    if ($conn->query($update_sql) === TRUE) {
        echo "Order status updated successfully!";
    } else {
        echo "Error updating order: " . $conn->error;
    }
}

// Delete order
if (isset($_GET['delete_id'])) {
    $order_id = $_GET['delete_id'];
    $delete_sql = "DELETE FROM orders WHERE id = $order_id";
    if ($conn->query($delete_sql) === TRUE) {
        echo "Order deleted successfully!";
    } else {
        echo "Error deleting order: " . $conn->error;
    }
}

// Fetch orders
$sql = "SELECT o.id, u.full_name AS user_name, p.name AS product_name, o.quantity, o.total_price, o.order_date, o.status
        FROM orders o
        JOIN users u ON o.user_id = u.id
        JOIN products p ON o.product_id = p.id";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Manage Orders</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Basic Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px 12px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .status-update-form select {
            padding: 5px;
        }
        .status-update-form button {
            padding: 5px 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        .status-update-form button:hover {
            background-color: #45a049;
        }
        .delete-btn {
            color: red;
            text-decoration: none;
        }
    </style>
</head>
<body>

    <div class="main">
        <h2>Manage Orders</h2>

        <!-- Orders Table -->
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>User</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Total Price</th>
                    <th>Order Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Display orders
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>" . $row['id'] . "</td>
                                <td>" . $row['user_name'] . "</td>
                                <td>" . $row['product_name'] . "</td>
                                <td>" . $row['quantity'] . "</td>
                                <td>" . $row['total_price'] . "</td>
                                <td>" . $row['order_date'] . "</td>
                                <td>" . $row['status'] . "</td>
                                <td>
                                    <form action='orders.php' method='POST' class='status-update-form'>
                                        <select name='status'>
                                            <option value='Pending' " . ($row['status'] == 'Pending' ? 'selected' : '') . ">Pending</option>
                                            <option value='Completed' " . ($row['status'] == 'Completed' ? 'selected' : '') . ">Completed</option>
                                            <option value='Cancelled' " . ($row['status'] == 'Cancelled' ? 'selected' : '') . ">Cancelled</option>
                                        </select>
                                        <input type='hidden' name='order_id' value='" . $row['id'] . "'>
                                        <button type='submit' name='update_status'>Update</button>
                                    </form>
                                    <a href='orders.php?delete_id=" . $row['id'] . "' class='delete-btn' onclick='return confirm(\"Are you sure you want to delete this order?\")'>Delete</a>
                                </td>
                            </tr>";
                    }
                } else {
                    echo "<tr><td colspan='8'>No orders found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
