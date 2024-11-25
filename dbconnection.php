<?php

$hostName = "sql102.infinityfree.com";
$dbUser = "if0_37784656";
$dbPassword = "bumgM0ZH5a"; // Typically empty in XAMPP
$dbName = "if0_37784656_pbdb";

// Create the database connection
$conn = mysqli_connect($hostName, $dbUser, $dbPassword, $dbName);

// Check if the connection is successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

?>
