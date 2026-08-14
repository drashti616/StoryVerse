<?php
ini_set('log_errors', 1);
ini_set('error_log', 'php-error.log');

require_once 'includes/db_config.php';

session_start();

if (!isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
    error_log("Attempted unauthorized account deletion.");
    header("Location: login.php?error=unauthorized_deletion");
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$status = 'error';
$error_message = '';

try {
    // Start a transaction so account deletion remains consistent.
    $pdo->beginTransaction();

    $sql_user = "UPDATE users SET is_deleted = 1, deleted_at = NOW() WHERE id = :user_id";
    $stmt_user = $pdo->prepare($sql_user);
    $stmt_user->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt_user->execute();

    $sql_messages = "UPDATE messages SET user_deleted = 1 WHERE user_id = :user_id";
    $stmt_messages = $pdo->prepare($sql_messages);
    $stmt_messages->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt_messages->execute();

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
    header("Location: profile.php?deletion_error=1");
    exit;
}
?>