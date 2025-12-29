<?php
// FILE: delete_account.php
// Handles the permanent deletion of the currently logged-in user account.

// Set up error logging to catch issues
ini_set('log_errors', 1);
ini_set('error_log', 'php-error.log'); // Or a path you prefer

// Make sure you have the correct path to your database configuration
require_once 'includes/db_config.php';

session_start();

// 1. SECURITY CHECK: Ensure user is logged in and the ID is a valid integer
if (!isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
    error_log("Attempted unauthorized account deletion.");
    header("Location: login.php?error=unauthorized_deletion");
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$status = 'error';
$error_message = '';

try {
    // Start a transaction to ensure all related data is deleted or none is.
    $pdo->beginTransaction();

    // 2. DELETE RELATED DATA FIRST
    // This is the CRITICAL part. Add all your related tables here.
    

    // Example: Delete user's profile pictures from a separate table if one exists
    // You would add more queries here based on your database design
    
    // 3. DELETE THE USER RECORD ITSELF
    $sql_user = "DELETE FROM users WHERE id = :user_id";
    $stmt_user = $pdo->prepare($sql_user);
    $stmt_user->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt_user->execute();

    // If all queries were successful, commit the transaction
    $pdo->commit();
    $status = 'success';

} catch (PDOException $e) {
    // If any query failed, roll back all changes
    $pdo->rollBack();
    $error_message = $e->getMessage();
    error_log("Account deletion failed for user ID $user_id. Error: " . $error_message);
}

// 4. LOGOUT AND REDIRECT
if ($status === 'success') {
    // Clear session variables and destroy the session
    $_SESSION = array();
    session_destroy();

    // Redirect to login with a success message
    header("Location: login.php?deletion_success=1");
    exit;
} else {
    // Redirect back to profile with an error message
    // Note: Never expose the full error message in the URL
    header("Location: profile.php?deletion_error=1");
    exit;
}
?>