<?php
// FILE: includes/db_config.php
// This file establishes the connection to your MySQL database.
// !!! IMPORTANT: Update with your actual database credentials.
// The database name should match the one created by the SQL schema, which is 'audiobooks'.
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // <-- Change this to your MySQL username
define('DB_PASSWORD', ''); // <-- Change this to your MySQL password
define('DB_NAME', 'audiobooks'); // <-- Ensure this matches your database name

try {
    $dsn = "mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}
?>