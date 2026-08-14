<?php
// FILE: upload_profile_picture.php
// Handles uploading and removing profile pictures.

session_start();

require_once 'includes/db_config.php';

// Folder where profile pictures will be stored
$uploadDir = __DIR__ . '/profile_pictures/';

$response = [
    'success' => false,
    'message' => 'An unknown error occurred.'
];

// Check login
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header("Location: login.php");
    exit;
}

// Determine table and redirect page
if ($_SESSION['user_role'] === 'user') {
    $tableName = 'users';
    $redirectUrl = 'profile.php';
} elseif ($_SESSION['user_role'] === 'admin') {
    $tableName = 'admins';
    $redirectUrl = 'admin_profile.php';
} else {
    header("Location: login.php");
    exit;
}

/* ============================================================
   REMOVE PROFILE PICTURE
   ============================================================ */
if (isset($_POST['remove_submit'])) {

    try {

        $sql = "SELECT profile_image_path FROM {$tableName} WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();

        $currentPath = $stmt->fetchColumn();

        // Delete image from server
        if (!empty($currentPath)) {

            $fullPath = __DIR__ . '/' . $currentPath;

            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }

        // Remove path from database
        $sql = "UPDATE {$tableName}
                SET profile_image_path = ''
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();

        header("Location: {$redirectUrl}?upload=removed");
        exit;

    } catch (PDOException $e) {

        header("Location: {$redirectUrl}?upload=dberror");
        exit;
    }
}

/* ============================================================
   UPLOAD PROFILE PICTURE
   ============================================================ */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {

    header('Content-Type: application/json');

    $file = $_FILES['profile_picture'];

    // Create folder if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Upload error
    if ($file['error'] !== UPLOAD_ERR_OK) {

        $response['message'] = 'Upload error. Code: ' . $file['error'];

        echo json_encode($response);
        exit;
    }

    $allowed = ['jpg', 'jpeg', 'png', 'gif'];

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($extension, $allowed)) {

        $response['message'] = 'Only JPG, JPEG, PNG and GIF files are allowed.';

        echo json_encode($response);
        exit;
    }

    // Generate unique filename
    $newFileName = uniqid('profile_', true) . '.' . $extension;

    // Full server path
    $destination = $uploadDir . $newFileName;

    // Relative path stored in database
    $dbPath = 'profile_pictures/' . $newFileName;

    if (move_uploaded_file($file['tmp_name'], $destination)) {

        try {

            $sql = "UPDATE {$tableName}
                    SET profile_image_path = :path
                    WHERE id = :id";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':path', $dbPath);
            $stmt->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
            $stmt->execute();

            $response['success'] = true;
            $response['message'] = 'Profile picture updated successfully.';
            $response['image_path'] = $dbPath;

        } catch (PDOException $e) {

            $response['message'] = 'Database error: ' . $e->getMessage();
        }

    } else {

        $response['message'] = 'Failed to move uploaded file.';
    }

    echo json_encode($response);
    exit;
}

// Default JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;