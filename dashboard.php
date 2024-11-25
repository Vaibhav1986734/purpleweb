<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'login_register');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}



$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
if ($page === 'order') {
    include('order.php');  
} 
elseif($page === 'manage_users') {
    include('manage_users.php');  
} 
elseif($page === 'manage_products') {
    include('manage_products.php');  
} 
elseif($page === 'admin_dashboard') {
    include('admin_dashboard.php');  
} 
else {
    // Default dashboard content
    echo "<div class='main'><h2>Admin Dashboard</h2><p>Welcome to the admin dashboard.</p></div>";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
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
            display: flex;
            transition: margin-left 0.3s ease; /* Smooth transition for main content */
        }

        /* Topbar Styling */
        .topbar {
            position: fixed;
            top: 0;
            width: 100%;
            height: 60px;
            background-color: #333;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            z-index: 1000;
        }

        .topbar h1 {
            font-size: 20px;
            margin-left: 10px;
        }

        .toggle-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 10px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            width: 50px; /* Width of the button */
            height: 40px; /* Height of the button */
        }

        .bar {
            display: block;
            width: 100%;
            height: 4px;
            background-color: white;
            border-radius: 2px;
            transition: 0.3s; /* Smooth transition effect for the bars */
        }

        /* Sidebar Styling */
        .sidebar {
            width: 200px;
            height: 100vh;
            background-color: #2196f3cc;
            padding-top: 80px;
            position: fixed;
            left: -200px; /* Initially hidden */
            transition: left 0.3s ease;
        }

        .sidebar.open {
            left: 0; /* Sidebar is visible */
        }

        .sidebar a {
            display: block;
            color: white;
            padding: 15px;
            text-decoration: none;
            font-size: 16px;
        }

        .sidebar a:hover {
            background-color: #45a049;
        }

        /* Main Content Styling */
        .main {
            margin-left: 0; /* Adjust for sidebar visibility */
            padding: 80px 20px 20px 20px;
            width: 100%;
            transition: margin-left 0.3s ease; /* Smooth transition for main content */
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

        <h1>Admin Dashboard</h1>
    </div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <a href="dashboard.php?page=admin_dashboard">Dashboard</a> 
        <a href="dashboard.php?page=manage_users">Manage Users</a>
        <a href="dashboard.php?page=manage_products">Manage Products</a>
        <a href="dashboard.php?page=order">Orders</a> <!-- Correct link to orders management -->
        <a href="#">Settings</a>
        <a href="logout.php">Logout</a>
    </div>


    <!-- JavaScript to Toggle Sidebar -->
    <script>
        function toggleSidebar() {
            var sidebar = document.getElementById("sidebar");
            sidebar.classList.toggle("open");

            // Adjust the main content margin when sidebar is toggled
            var main = document.querySelector(".main");
            if (sidebar.classList.contains("open")) {
                main.style.marginLeft = "200px"; // Move content to the right when sidebar is open
            } else {
                main.style.marginLeft = "0"; // Move content back to the left when sidebar is closed
            }
        }
    </script>
</body>
</html>
