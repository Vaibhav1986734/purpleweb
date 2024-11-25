<?php
session_start();

// Redirect if user or admin is already logged in
if (isset($_SESSION["user"])) {
    header("Location: index.php");
    exit();
} elseif (isset($_SESSION["admin"])) {
    header("Location: dashboard.php");
    exit();
}

require_once "dbconnection.php"; // Database connection

// Error Reporting for debugging (can be turned off in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    // User login
    if (isset($_POST["login"])) {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = mysqli_stmt_init($conn);
        if (mysqli_stmt_prepare($stmt, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($result);

            if ($user && password_verify($password, $user["password"])) {
                $_SESSION["user"] = $user["id"];
                echo "<script>alert('Login successful!'); window.location.href = 'index.php';</script>";
                exit();
            } else {
                echo "<script>alert('Email or password is incorrect.');</script>";
            }
        } else {
            echo "<script>alert('SQL Error: " . mysqli_error($conn) . "');</script>";
        }
    }

    // Admin login
    elseif (isset($_POST["admin_login"])) {
        $sql = "SELECT * FROM admins WHERE email = ?";
        $stmt = mysqli_stmt_init($conn);
        if (mysqli_stmt_prepare($stmt, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $admin = mysqli_fetch_assoc($result);
            if (!$admin) {
                echo "<script>alert('No admin found with this email.');</script>";
            } else if ($admin && password_verify($password, $admin["password"])) {
                $_SESSION["admin"] = $admin["id"];
                $_SESSION["role"] = 'admin';
                echo "<script>alert('Admin Login successful!'); window.location.href = 'dashboard.php?page=admin_dashboard';</script>";
                exit();
            } else {
                echo "<script>alert('Email or password is incorrect.');</script>";
            }
        } else {
            echo "<script>alert('SQL Error: " . mysqli_error($conn) . "');</script>";
        }
    }

    // User Registration
    elseif (isset($_POST["register"])) {
        $fullname = $_POST["fullname"];
        $email = $_POST["email"];
        $address = $_POST["address"]; // Get the address from the form
        $password_repeat = $_POST["repeat_password"];

        if ($password !== $password_repeat) {
            echo "<script>alert('Passwords do not match.');</script>";
        } else {
            $password_hashed = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (full_name, email, address, password) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_stmt_init($conn);
            if (mysqli_stmt_prepare($stmt, $sql)) {
                mysqli_stmt_bind_param($stmt, "ssss", $fullname, $email, $address, $password_hashed);
                if (mysqli_stmt_execute($stmt)) {
                    echo "<script>alert('Registration successful! You can now log in.');</script>";
                } else {
                    echo "<script>alert('Error during registration. Please try again.');</script>";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Register</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body>
<style>
/* Basic Reset */
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'Roboto', sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; }
.container { display: flex; justify-content: center; align-items: center; width: 100%; height: 100%; background:url("images/banner_1.jpg"); background-position: center; background-repeat: no-repeat; background-size: cover; }
.forms-container { width: 100%; max-width: 400px; background-color: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
h2 { text-align: center; margin-bottom: 20px; }
.input-group { margin-bottom: 20px; }
.input-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; }
.btn { width: 100%; padding: 12px; background-color: #667eea; color: #fff; border: none; border-radius: 5px; font-size: 18px; cursor: pointer; transition: 0.3s; }
.btn:hover { background-color: #4e5ce6; }
.toggle-form { text-align: center; margin-top: 15px; color: #888; }
.toggle-form span { color: #667eea; cursor: pointer; }
</style>

<div class="container">
    <div class="forms-container">

        <!-- User Login Form -->
        <div class="login-form">
            <h2>User Login</h2>
            <form action="" method="POST">
                <div class="input-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" placeholder="Email" required />
                </div>
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" placeholder="Password" required />
                </div>
                <button type="submit" name="login" class="btn">Login</button>
                <p class="toggle-form">Don't have an account? <span id="switch-to-register">Register</span></p>
                <p class="toggle-form">Admin? <span id="switch-to-admin">Login as Admin</span></p>
            </form>
        </div>

        <!-- Admin Login Form -->
        <div class="admin-login-form" style="display: none;">
            <h2>Admin Login</h2>
            <form action="" method="POST">
                <div class="input-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" placeholder="Email" required />
                </div>
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" placeholder="Password" required />
                </div>
                <button type="submit" name="admin_login" class="btn">Login as Admin</button>
                <p class="toggle-form">Go back to <span id="switch-to-user-login">User Login</span></p>
            </form>
        </div>

        <!-- Register Form -->
        <div class="register-form" style="display: none;">
            <h2>Register</h2>
            <form action="" method="POST">
                <div class="input-group">
                    <label for="fullname">Full Name</label>
                    <input type="text" name="fullname" id="fullname" placeholder="Full Name" required />
                </div>
                <div class="input-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" placeholder="Email" required />
                </div>
                <div class="input-group">
                    <label for="address">Address</label>
                    <input type="text" name="address" id="address" placeholder="Address" required />
                </div>
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" placeholder="Password" required />
                </div>
                <div class="input-group">
                    <label for="repeat_password">Repeat Password</label>
                    <input type="password" name="repeat_password" id="repeat_password" placeholder="Repeat Password" required />
                </div>
                <button type="submit" name="register" class="btn">Register</button>
                <p class="toggle-form">Already have an account? <span id="switch-to-login">Login</span></p>
            </form>
        </div>

    </div>
</div>

<script>
    // Switch between forms
    document.getElementById('switch-to-register').onclick = function () {
        document.querySelector('.login-form').style.display = 'none';
        document.querySelector('.register-form').style.display = 'block';
    };
    document.getElementById('switch-to-login').onclick = function () {
        document.querySelector('.register-form').style.display = 'none';
        document.querySelector('.login-form').style.display = 'block';
    };
    document.getElementById('switch-to-admin').onclick = function () {
        document.querySelector('.login-form').style.display = 'none';
        document.querySelector('.admin-login-form').style.display = 'block';
    };
    document.getElementById('switch-to-user-login').onclick = function () {
        document.querySelector('.admin-login-form').style.display = 'none';
        document.querySelector('.login-form').style.display = 'block';
    };
</script>

</body>
</html>
