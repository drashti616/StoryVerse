<?php
// FILE: includes/db_config.php
// This file establishes the connection to your MySQL database.

define('DB_SERVER', 'YOUR_DATABASE_HOST_HERE');   // e.g., localhost 
define('DB_USERNAME', 'YOUR_DATABASE_USER_HERE'); // Your database username
define('DB_PASSWORD', 'YOUR_DATABASE_PASS_HERE'); // Your database password
define('DB_NAME', 'YOUR_DATABASE_NAME_HERE');     // Your database name

try {
    $dsn = "mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}
?>
