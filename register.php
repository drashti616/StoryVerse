<?php
// FILE: register.php
// Handles user registration, inserting into the 'users' table. Admins cannot register here.

require_once 'includes/db_config.php';

$error_message = '';
$success_message = '';

// Initialize variables to prevent "Undefined variable" warnings
$username = '';
$email = '';
$password = ''; // This is for the form value, not the hashed password
$confirm_password = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assign submitted values to variables immediately for display in form, then trim
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // --- Username Validation ---
    if (empty($username)) {
        $error_message = "Please enter a username.";
    } elseif (strlen($username) < 6) { // Added minimum length for username
        $error_message = "Username must be at least 6 characters long.";
    } elseif (preg_match('/[A-Z]/', $username)) { // Disallow uppercase
        $error_message = "Username must not contain any uppercase letters.";
    } elseif (!preg_match('/[a-z]/', $username)) { // Require at least one lowercase
        $error_message = "Username must contain at least one lowercase letter.";
    } elseif (!preg_match('/[0-9]/', $username)) { // Require at least one number
        $error_message = "Username must contain at least one number.";
    } elseif (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?~` ]/', $username)) { // Require at least one symbol
        $error_message = "Username must contain at least one special character.";
    } elseif (!preg_match('/^[a-z0-9!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?~` ]+$/', $username)) { // Only allowed characters
        $error_message = "Username contains invalid characters. Only lowercase letters, numbers, and symbols are allowed.";
    } else {
        // Check if username already exists in the 'users' table
        $sql = "SELECT id FROM users WHERE username = :username";
        if ($stmt = $pdo->prepare($sql)) {
            $stmt->bindParam(":username", $username, PDO::PARAM_STR);
            if ($stmt->execute()) {
                if ($stmt->rowCount() == 1) {
                    $error_message = "This username is already taken.";
                }
            } else {
                $error_message = "Oops! Something went wrong. Please try again later.";
            }
        }
    }
    // --- End Username Validation ---

    // --- Email Validation ---
    if (empty($error_message)) { // Only proceed if no username error
        if (empty($email)) {
            $error_message = "Please enter an email.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Please enter a valid email format.";
        } else {
            // Check if email already exists in the 'users' table
            $sql = "SELECT id FROM users WHERE email = :email";
            if ($stmt = $pdo->prepare($sql)) {
                $stmt->bindParam(":email", $email, PDO::PARAM_STR);
                if ($stmt->execute()) {
                    if ($stmt->rowCount() == 1) {
                        $error_message = "This email is already registered.";
                    }
                } else {
                    $error_message = "Oops! Something went wrong. Please try again later.";
                }
            }
        }
    }
    // --- End Email Validation ---

    // --- Password Validation ---
    if (empty($error_message)) { // Only proceed if no username or email error
        if (empty($password) || empty($confirm_password)) {
            $error_message = "Please fill in all password fields.";
        } elseif ($password !== $confirm_password) {
            $error_message = "Passwords do not match.";
        } elseif (strlen($password) < 8) {
            $error_message = "Password must be at least 8 characters long.";
        } elseif (!preg_match('/[A-Z]/', $password)) {
            $error_message = "Password must contain at least one uppercase letter.";
        } elseif (!preg_match('/[a-z]/', $password)) {
            $error_message = "Password must contain at least one lowercase letter.";
        } elseif (!preg_match('/[0-9]/', $password)) {
            $error_message = "Password must contain at least one number.";
        } elseif (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?~` ]/', $password)) {
            $error_message = "Password must contain at least one special character.";
        }
    }
    // --- End Password Validation ---

    // If no errors, insert into 'users' table
    if (empty($error_message)) {
        $sql_insert = "INSERT INTO users (username, email, password_hash) VALUES (:username, :email, :password_hash)";

        if ($stmt_insert = $pdo->prepare($sql_insert)) {
            $password_hash = password_hash($password, PASSWORD_DEFAULT); // Hash the password

            $stmt_insert->bindParam(":username", $username);
            $stmt_insert->bindParam(":email", $email);
            $stmt_insert->bindParam(":password_hash", $password_hash);
            
            if ($stmt_insert->execute()) {
                // Automatically log in the user after successful registration
                session_start();
                $_SESSION['user_id'] = $pdo->lastInsertId(); // Get the ID of the newly inserted user
                $_SESSION['username'] = $username;
                $_SESSION['user_role'] = 'user'; // Explicitly set role as 'user'

                // Redirect to the browse page after successful registration
                header("location: profile.php");
                exit;
            } else {
                $error_message = "Oops! Something went wrong. Please try again later.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-900 text-white flex items-center justify-center min-h-screen">
    <div class="bg-gray-800 p-8 rounded-lg shadow-xl w-full max-w-md">
        <h1 class="text-3xl font-bold text-center mb-6">Create an Account</h1>
        <?php if (!empty($error_message)): ?>
            <div class="bg-red-500 text-white p-3 rounded-lg mb-4" id="server-error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <?php if (!empty($success_message)): ?>
            <div class="bg-green-500 text-white p-3 rounded-lg mb-4"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="space-y-4" onsubmit="return validateForm()" autocomplete="off">
            <div>
                <label for="username" class="block text-gray-400 mb-2">Username</label>
                <input type="text" name="username" id="username" class="w-full bg-gray-700 text-white p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?php echo htmlspecialchars($username); ?>" autocomplete="username" required>
                <div id="username-feedback" class="text-sm mt-1"></div> <!-- Feedback for username strength -->
            </div>
            <div>
                <label for="email" class="block text-gray-400 mb-2">Email</label>
                <input type="email" name="email" id="email" class="w-full bg-gray-700 text-white p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?php echo htmlspecialchars($email); ?>" autocomplete="email" required>
                <div id="email-feedback" class="text-sm mt-1"></div> <!-- Feedback for email validation -->
            </div>
            <div>
                <label for="password" class="block text-gray-400 mb-2">Password</label>
                <input type="password" name="password" id="password" class="w-full bg-gray-700 text-white p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" autocomplete="new-password" required>
                <div id="password-feedback" class="text-sm mt-1"></div> <!-- Feedback for password strength -->
            </div>
            <div>
                <label for="confirm_password" class="block text-gray-400 mb-2">Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" class="w-full bg-gray-700 text-white p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" autocomplete="new-password" required>
                <div id="confirm-password-feedback" class="text-sm mt-1"></div> <!-- Feedback for confirm password -->
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white p-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors">Create Account</button>
        </form>
        <p class="text-center text-gray-400 mt-4">Already have an account? <a href="login.php" class="text-blue-400 hover:underline">Log in here</a></p>
    </div>
    <script>
        // Clear all form fields (non-hidden) on pageshow (including BFCache restores)
        (function() {
            function clearAllFormFields() {
                var form = document.querySelector('form');
                if (!form) return;
                var inputs = form.querySelectorAll('input, textarea, select');
                inputs.forEach(function(el) {
                    if (el.tagName === 'INPUT') {
                        var type = (el.getAttribute('type') || '').toLowerCase();
                        if (type === 'hidden' || type === 'submit' || type === 'button' || type === 'checkbox' || type === 'radio' || type === 'file') {
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
        // Client-side username strength validation
        function validateUsernameStrength(username) {
            const checks = {
                length: username.length >= 6,
                noUppercase: !/[A-Z]/.test(username),
                lowercase: /[a-z]/.test(username),
                number: /[0-9]/.test(username),
                symbol: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?~` ]/.test(username),
                validChars: /^[a-z0-9!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?~` ]+$/.test(username)
            };

            let feedback = [];
            if (!checks.length) feedback.push('At least 6 characters');
            if (!checks.noUppercase) feedback.push('No uppercase letters');
            if (!checks.lowercase) feedback.push('One lowercase letter');
            if (!checks.number) feedback.push('One number');
            if (!checks.symbol) feedback.push('One symbol');
            if (!checks.validChars && username.length > 0) feedback.push('Only lowercase letters, numbers, and symbols');

            return {
                isValid: checks.length && checks.noUppercase && checks.lowercase && checks.number && checks.symbol && checks.validChars,
                feedback: feedback.join(', ')
            };
        }

        // Client-side password strength validation
        function validatePasswordStrength(password) {
            const checks = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /[0-9]/.test(password),
                symbol: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?~` ]/.test(password) // Common symbols
            };

            let feedback = [];
            if (!checks.length) feedback.push('At least 8 characters');
            if (!checks.uppercase) feedback.push('One uppercase letter');
            if (!checks.lowercase) feedback.push('One lowercase letter');
            if (!checks.number) feedback.push('One number');
            if (!checks.symbol) feedback.push('One symbol');

            return {
                isValid: Object.values(checks).every(Boolean),
                feedback: feedback.join(', ')
            };
        }

        const usernameInput = document.getElementById('username');
        const usernameFeedback = document.getElementById('username-feedback');
        const emailInput = document.getElementById('email');
        const emailFeedback = document.getElementById('email-feedback');
        const regPasswordInput = document.getElementById('password');
        const regPasswordFeedback = document.getElementById('password-feedback');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const confirmPasswordFeedback = document.getElementById('confirm-password-feedback');
        const serverErrorMessage = document.getElementById('server-error-message');

        let formSubmittedOnce = false; // Flag to control when real-time feedback starts

        // Function to clear server error message when user starts typing in any field
        function clearServerError() {
            if (serverErrorMessage) {
                serverErrorMessage.style.display = 'none';
            }
        }

        // Add event listeners to clear server error message on input focus/keyup
        usernameInput.addEventListener('focus', clearServerError);
        emailInput.addEventListener('focus', clearServerError);
        regPasswordInput.addEventListener('focus', clearServerError);
        confirmPasswordInput.addEventListener('focus', clearServerError);

        // Real-time validation for username (only after first submission attempt)
        usernameInput.addEventListener('keyup', () => {
            clearServerError();
            if (formSubmittedOnce) { // Only show/update feedback if form has been submitted once
                const result = validateUsernameStrength(usernameInput.value);
                if (result.isValid) {
                    usernameFeedback.textContent = ''; // Clear feedback if valid
                    usernameFeedback.className = 'text-sm mt-1 text-green-400'; // Optional: show success
                } else {
                    usernameFeedback.className = 'text-sm mt-1 text-red-400';
                    usernameFeedback.textContent = 'Needs: ' + result.feedback;
                }
            }
        });

        // Real-time validation for password (only after first submission attempt)
        regPasswordInput.addEventListener('keyup', () => {
            clearServerError();
            if (formSubmittedOnce) { // Only show/update feedback if form has been submitted once
                const result = validatePasswordStrength(regPasswordInput.value);
                if (result.isValid) {
                    regPasswordFeedback.textContent = ''; // Clear feedback if valid
                    regPasswordFeedback.className = 'text-sm mt-1 text-green-400'; // Optional: show success
                } else {
                    regPasswordFeedback.className = 'text-sm mt-1 text-red-400';
                    regPasswordFeedback.textContent = 'Needs: ' + result.feedback;
                }
                // Also check confirm password if password changes
                if (confirmPasswordInput.value.length > 0) {
                    checkConfirmPassword();
                }
            }
        });

        // Real-time validation for confirm password (only after first submission attempt)
        confirmPasswordInput.addEventListener('keyup', () => {
            clearServerError();
            if (formSubmittedOnce) { // Only show/update feedback if form has been submitted once
                checkConfirmPassword();
            }
        });

        // Real-time validation for email (only after first submission attempt)
        emailInput.addEventListener('keyup', () => {
            clearServerError();
            if (formSubmittedOnce) { // Only show/update feedback if form has been submitted once
                validateEmail();
            }
        });

        // Function to validate email format
        function validateEmail() {
            const emailValue = emailInput.value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; // Basic email regex

            if (emailValue === '') {
                emailFeedback.className = 'text-sm mt-1 text-red-400';
                emailFeedback.textContent = 'Please enter an email.';
                return false;
            } else if (!emailRegex.test(emailValue)) {
                emailFeedback.className = 'text-sm mt-1 text-red-400';
                emailFeedback.textContent = 'Please enter a valid email format.';
                return false;
            } else {
                emailFeedback.textContent = ''; // Clear feedback if valid
                emailFeedback.className = 'text-sm mt-1 text-green-400'; // Optional: show success
                return true;
            }
        }

        // Function to check if confirm password matches main password
        function checkConfirmPassword() {
            if (confirmPasswordInput.value.trim() === '') {
                confirmPasswordFeedback.className = 'text-sm mt-1 text-red-400';
                confirmPasswordFeedback.textContent = 'Please confirm your password.';
                return false;
            } else if (confirmPasswordInput.value !== regPasswordInput.value) {
                confirmPasswordFeedback.className = 'text-sm mt-1 text-red-400';
                confirmPasswordFeedback.textContent = 'Passwords do not match.';
                return false;
            } else {
                confirmPasswordFeedback.textContent = ''; // Clear feedback if valid
                confirmPasswordFeedback.className = 'text-sm mt-1 text-green-400'; // Optional: show success
                return true;
            }
        }

        // Main form validation function called on submit button click
        function validateForm() {
            clearServerError(); // Clear any existing server errors before re-validating
            formSubmittedOnce = true; // Set flag to true after first submission attempt

            let isValid = true;

            // Validate Username
            const usernameResult = validateUsernameStrength(usernameInput.value);
            if (!usernameResult.isValid) {
                usernameFeedback.className = 'text-sm mt-1 text-red-400';
                usernameFeedback.textContent = 'Needs: ' + usernameResult.feedback;
                isValid = false;
            } else {
                usernameFeedback.textContent = ''; // Clear feedback if valid
                usernameFeedback.className = 'text-sm mt-1 text-green-400'; // Optional: show success
            }

            // Validate Email
            if (!validateEmail()) { // Call the dedicated email validation function
                isValid = false;
            }

            // Validate Password
            const passwordResult = validatePasswordStrength(regPasswordInput.value);
            if (!passwordResult.isValid) {
                regPasswordFeedback.className = 'text-sm mt-1 text-red-400';
                regPasswordFeedback.textContent = 'Needs: ' + passwordResult.feedback;
                isValid = false;
            } else {
                regPasswordFeedback.textContent = ''; // Clear feedback if valid
                regPasswordFeedback.className = 'text-sm mt-1 text-green-400'; // Optional: show success
            }

            // Validate Confirm Password
            if (!checkConfirmPassword()) { // Call the dedicated confirm password validation function
                isValid = false;
            }
            
            return isValid; // Prevent form submission if validation fails
        }
    </script>
</body>
</html>
