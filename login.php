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

const LOGIN_IDENTIFIER_MAX_LENGTH = 254;
const LOGIN_PASSWORD_MAX_LENGTH = 128;

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
    $identifier_input = $_POST['identifier'] ?? '';
$password_input = $_POST['password'] ?? '';

$identifier = is_string($identifier_input)
    && strlen($identifier_input) <= LOGIN_IDENTIFIER_MAX_LENGTH
    ? trim($identifier_input)
    : '';

$password = is_string($password_input)
    && strlen($password_input) <= LOGIN_PASSWORD_MAX_LENGTH
    ? trim($password_input)
    : '';

if (!is_string($identifier_input) || !is_string($password_input)) {
    $error_message = "Invalid form submission.";
} elseif (strlen($identifier_input) > LOGIN_IDENTIFIER_MAX_LENGTH) {
    $error_message = "Username or email must not exceed 254 characters.";
} elseif (strlen($password_input) > LOGIN_PASSWORD_MAX_LENGTH) {
    $error_message = "Password must not exceed 128 characters.";
} elseif (empty($identifier) || empty($password)) {
    $error_message = "Please fill in all fields.";
} else {
        $login_successful = false;

        // Step 1: Check the 'users' table
        $sql_user = "SELECT id, username, email, password_hash FROM users WHERE (username = :identifier OR email = :identifier) AND is_deleted = 0 LIMIT 1";
        if ($stmt_user = $pdo->prepare($sql_user)) {
            $stmt_user->bindParam(":identifier", $identifier);
            if ($stmt_user->execute()) {
                if ($stmt_user->rowCount() == 1) {
                    $user_data = $stmt_user->fetch(PDO::FETCH_ASSOC);
                    if (password_verify($password, $user_data['password_hash'])) {
                        // User login successful
                        // Prevent session fixation and ensure a fresh session id
                        session_regenerate_id(true);
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
                            // Prevent session fixation and ensure a fresh session id
                            session_regenerate_id(true);
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="includes/responsive.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-900 text-white flex items-center justify-center min-h-screen overflow-x-hidden px-4 py-8">
    <div class="bg-gray-800 p-6 sm:p-8 rounded-lg shadow-xl w-full max-w-md">
        <h1 class="text-2xl sm:text-3xl font-bold text-center mb-6">StoryVerse</h1>
        <?php if (!empty($error_message)): ?>
            <div class="bg-red-500 text-white p-3 rounded-lg mb-4" id="server-error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <?php if ($auth_required): ?>
            <div class="bg-yellow-500 text-white p-3 rounded-lg mb-4" id="auth-required-message">You must be logged in to access this page.</div>
        <?php endif; ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="space-y-4" autocomplete="off">
            <div>
                <label for="identifier" class="block text-gray-400 mb-2">Username or Email</label>
                <input type="text" name="identifier" id="identifier" class="w-full bg-gray-700 text-white p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" autocomplete="username" maxlength="254" required>
            </div>
           <div>
    <label for="password" class="block text-gray-400 mb-2">Password</label>

    <div class="relative">
        <input
            type="password"
            name="password"
            id="password"
			maxlength="128"
            class="w-full bg-gray-700 text-white p-3 pr-12 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            autocomplete="current-password"
            required>

        <button
            type="button"
            id="togglePassword"
            class="absolute inset-y-0 right-0 flex items-center px-4 text-gray-400 hover:text-white">

            <i class="fa-solid fa-eye" id="toggleIcon"></i>

        </button>
    </div>
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
            document.addEventListener('DOMContentLoaded', function() { clearAllFormFields(); });
        })();
    </script>
    <script>
const passwordField = document.getElementById("password");
const togglePassword = document.getElementById("togglePassword");
const toggleIcon = document.getElementById("toggleIcon");

togglePassword.addEventListener("click", function () {

    if (passwordField.type === "password") {

        passwordField.type = "text";
        toggleIcon.classList.remove("fa-eye");
        toggleIcon.classList.add("fa-eye-slash");

    } else {

        passwordField.type = "password";
        toggleIcon.classList.remove("fa-eye-slash");
        toggleIcon.classList.add("fa-eye");

    }

});
</script>
</body>
</html>
