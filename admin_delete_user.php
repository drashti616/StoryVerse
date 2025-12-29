<?php
// FILE: admin_delete_user.php
// Admin-side script to delete a user account and associated data.

require_once 'includes/db_config.php';
session_start();

// 1. SECURITY: Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("location: login.php");
    exit;
}

// 2. VALIDATION: Check for a valid user ID from the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("location: manage_users.php?error=invalid_user_id");
    exit;
}

$user_id_to_delete = (int)$_GET['id'];
$status = 'error';

// Optional: Prevent an admin from deleting themselves
if ($user_id_to_delete === $_SESSION['user_id']) {
    header("location: manage_users.php?error=self_deletion_not_allowed");
    exit;
}

try {
    $pdo->beginTransaction();


    // 4. DELETE THE USER RECORD ITSELF
    $sql_user = "DELETE FROM users WHERE id = :user_id";
    $stmt_user = $pdo->prepare($sql_user);
    $stmt_user->bindParam(':user_id', $user_id_to_delete, PDO::PARAM_INT);
    $stmt_user->execute();

    $pdo->commit();
    $status = 'success';

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Admin deletion failed for user ID $user_id_to_delete. Error: " . $e->getMessage());
}

// 5. REDIRECT BACK TO USER LIST WITH STATUS
if ($status === 'success') {
    header("Location: manage_users.php?deletion_success=1");
    exit;
} else {
    header("Location: manage_users.php?deletion_error=1");
    exit;
}
?>