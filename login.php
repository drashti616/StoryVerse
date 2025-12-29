<?php
// FILE: login.php
// A single login page for both regular users and administrators.
// This version includes a 'Continue as Guest' option.

// Set cache-control headers to prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Require the database configuration file
require_once 'includes/db_config.php';

// Start the session
session_start();

// Handle 'Continue as Guest' login
if (isset($_GET['action']) && $_GET['action'] === 'guest') {
    // Set a guest session variable
    $_SESSION['is_guest'] = true;
    $_SESSION['username'] = 'Guest';
    header("Location: browse.php");
    exit;
}

// Check for authentication required message from other pages
$auth_required = isset($_GET['auth_required']);

// Redirect to profile page if the user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: browse.php");
    exit;
}

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identifier = trim($_POST['identifier']); // Can be username or email
    $password = trim($_POST['password']);

    if (empty($identifier) || empty($password)) {
        $error_message = "Please fill in all fields.";
    } else {
        $login_successful = false;

        // Step 1: Check the 'users' table
        $sql_user = "SELECT id, username, email, password_hash FROM users WHERE username = :identifier OR email = :identifier LIMIT 1";
        if ($stmt_user = $pdo->prepare($sql_user)) {
            $stmt_user->bindParam(":identifier", $identifier);
            if ($stmt_user->execute()) {
                if ($stmt_user->rowCount() == 1) {
                    $user_data = $stmt_user->fetch(PDO::FETCH_ASSOC);
                    if (password_verify($password, $user_data['password_hash'])) {
                        // User login successful
                        $_SESSION['user_id'] = $user_data['id'];
                        $_SESSION['username'] = $user_data['username'];
                        $_SESSION['email'] = $user_data['email'];
                        $_SESSION['user_role'] = 'user';
                        unset($_SESSION['is_guest']); // Remove guest status if they log in
                        $login_successful = true;
                    }
                }
            }
            $stmt_user = null;
        }

        // Step 2: If user login failed, check the 'admins' table
        if (!$login_successful) {
            $sql_admin = "SELECT id, username, email, password_hash FROM admins WHERE username = :identifier OR email = :identifier LIMIT 1";
            if ($stmt_admin = $pdo->prepare($sql_admin)) {
                $stmt_admin->bindParam(":identifier", $identifier);
                if ($stmt_admin->execute()) {
                    if ($stmt_admin->rowCount() == 1) {
                        $admin_data = $stmt_admin->fetch(PDO::FETCH_ASSOC);
                        if (password_verify($password, $admin_data['password_hash'])) {
                            // Admin login successful
                            $_SESSION['user_id'] = $admin_data['id'];
                            $_SESSION['username'] = $admin_data['username'];
                            $_SESSION['email'] = $admin_data['email'];
                            $_SESSION['user_role'] = 'admin';
                            unset($_SESSION['is_guest']); // Remove guest status if they log in
                            $login_successful = true;
                        }
                    }
                }
                $stmt_admin = null;
            }
        }
        
        // Final redirection based on login status and role
        if ($login_successful) {
            if ($_SESSION['user_role'] === 'admin') {
                header("location: existing_audiobooks.php");
            } else {
                header("location: browse.php");
            }
            exit;
        } else {
            $error_message = "Invalid username/email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StoryVerse Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-900 text-white flex items-center justify-center min-h-screen">
    <div class="bg-gray-800 p-8 rounded-lg shadow-xl w-full max-w-md">
        <h1 class="text-3xl font-bold text-center mb-6">StoryVerse</h1>
        <?php if (!empty($error_message)): ?>
            <div class="bg-red-500 text-white p-3 rounded-lg mb-4" id="server-error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <?php if ($auth_required): ?>
            <div class="bg-yellow-500 text-white p-3 rounded-lg mb-4" id="auth-required-message">You must be logged in to access this page.</div>
        <?php endif; ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="space-y-4" autocomplete="off">
            <div>
                <label for="identifier" class="block text-gray-400 mb-2">Username or Email</label>
                <input type="text" name="identifier" id="identifier" class="w-full bg-gray-700 text-white p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" autocomplete="username" required>
            </div>
            <div>
                <label for="password" class="block text-gray-400 mb-2">Password</label>
                <input type="password" name="password" id="password" class="w-full bg-gray-700 text-white p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" autocomplete="current-password" required>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white p-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                Login
            </button>
        </form>
        <p class="text-center text-gray-400 text-sm mt-4">
            Don't have an account? <a href="register.php" class="text-blue-400 hover:underline">Create an account</a>
        </p>
       
    </div>
    <script>
        // Clear all form fields (non-hidden) when page is shown (including BFCache restores)
        (function() {
            function clearAllFormFields() {
                var form = document.querySelector('form');
                if (!form) return;
                var inputs = form.querySelectorAll('input, textarea, select');
                inputs.forEach(function(el) {
                    if (el.tagName === 'INPUT') {
                        var type = (el.getAttribute('type') || '').toLowerCase();
                        if (type === 'hidden' || type === 'submit' || type === 'button' || type === 'checkbox' || type === 'radio' || type === 'file') {
                            // Do not clear these types
                            return;
                        }
                        el.value = '';
                    } else if (el.tagName === 'TEXTAREA') {
                        el.value = '';
                    } else if (el.tagName === 'SELECT') {
                        el.selectedIndex = -1;
                    }
                });
            }
            window.addEventListener('pageshow', function() { clearAllFormFields(); });
            document.addEventListener('DOMContentLoaded', function() { clearAllFormFields(); });
        })();
    </script>
</body>
</html>
