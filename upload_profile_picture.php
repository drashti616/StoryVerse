<?php
// FILE: upload_profile_picture.php
// Handles the logic for uploading or removing a profile picture for users.

// Start a session
session_start();

// Include database configuration
require_once 'includes/db_config.php';

// Define the directory where uploaded pictures will be stored
$uploadDir = 'uploads/profile_pictures/';

// Set a default response, which will be updated later
$response = ['success' => false, 'message' => 'An unknown error occurred.'];

// Determine the table and redirect URL based on the user's role
$tableName = '';
$redirectUrl = '';

if (isset($_SESSION['user_role'])) {
    $role = $_SESSION['user_role'];
    if ($role === 'user') {
        $tableName = 'users';
        $redirectUrl = 'profile.php';
    } elseif ($role === 'admin') {
        $tableName = 'admins';
        $redirectUrl = 'admin_profile.php';
    }
} else {
    // No role found, redirect to login
    header("location: login.php");
    exit;
}

// Handle the "Remove Picture" action
if (isset($_POST['remove_submit'])) {
    try {
        // Fetch the current profile image path from the database
        $sql = "SELECT profile_image_path FROM {$tableName} WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();
        $current_path = $stmt->fetchColumn();

        // Delete the file if it exists and is not a default placeholder
        if (!empty($current_path) && file_exists($current_path)) {
            unlink($current_path);
        }

        // Update the database to clear the path
        $sql = "UPDATE {$tableName} SET profile_image_path = '' WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();

        // Redirect back to the profile page with a success message
        header("location: {$redirectUrl}?upload=removed");
        exit;
    } catch (PDOException $e) {
        header("location: {$redirectUrl}?upload=dberror");
        exit;
    }
}

// Handle the "Upload Picture" action for multipart form data
// This now correctly processes the file sent from the front-end's fetch request.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {

    // Check if the directory exists, if not, create it
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            $response['message'] = 'Failed to create upload directory.';
            echo json_encode($response);
            exit;
        }
    }

    $file = $_FILES['profile_picture'];

    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $response['message'] = 'File upload error occurred. Error code: ' . $file['error'];
        echo json_encode($response);
        exit;
    }

    $fileTmpName = $file['tmp_name'];
    $fileName = $file['name'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    $allowed = array('jpg', 'jpeg', 'png', 'gif');

    if (!in_array($fileExt, $allowed)) {
        $response['message'] = 'Invalid file type.';
        echo json_encode($response);
        exit;
    }
    
    $fileNameNew = uniqid('', true) . "." . $fileExt;
    $fileDestination = $uploadDir . $fileNameNew;
    
    if (move_uploaded_file($fileTmpName, $fileDestination)) {
        try {
            $sql = "UPDATE {$tableName} SET profile_image_path = :path WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':path', $fileDestination, PDO::PARAM_STR);
            $stmt->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
            $stmt->execute();

            $response['success'] = true;
            $response['message'] = 'Profile picture updated successfully.';
            $response['image_path'] = $fileDestination;
        } catch (PDOException $e) {
            $response['message'] = 'Database update failed: ' . $e->getMessage();
        }
    } else {
        $response['message'] = 'Failed to save the image file.';
    }

    // Send the JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

echo json_encode($response);
?>
