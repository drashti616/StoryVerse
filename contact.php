<?php
// FILE: contact.php
// A clean contact form page that saves messages to the database and shows a success pop-up.
// Added cache-control headers to prevent caching.
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Redirect to login if the user is not logged in.
if (!isset($_SESSION['user_id'])) {
    header("location: login.php");
    exit;
}

require_once 'includes/header.php';
require_once 'includes/db_config.php';

$name = $email = $subject = $message_body = '';
$name_err = $email_err = $message_err = '';
$success_message_flag = false;

// Check if the user is logged in to pre-fill the form
if (isset($_SESSION['user_id'])) {
    $name = $_SESSION['username'] ?? '';
    $email = $_SESSION['email'] ?? '';
} else {
    // If the user is not logged in, redirect them to the login page.
    // This is a crucial step to prevent unauthorized access to this page's content.
    header("Location: login.php");
    exit;
}

// Handle form submission to send a new message
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message_body = trim($_POST['message']);
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'] ?? 'Anonymous'; // Get the username from the session

    // Validate inputs
    if (empty($name) || empty($email) || empty($message_body)) {
        // You might want to handle errors differently, but for this simple version, we'll just stop
        die("Please fill in all required fields.");
    }

    try {
        $sql = "INSERT INTO messages (username, email, subject, message, user_id) VALUES (:username, :email, :subject, :message, :user_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':subject', $subject, PDO::PARAM_STR);
        $stmt->bindParam(':message', $message_body, PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $success_message_flag = true;
            $subject = $message_body = ''; 
        } else {
            // Handle insertion failure
            die("Error submitting message.");
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        die("An error occurred. Please try again later.");
    }
}
?>

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    body { font-family: 'Inter', sans-serif; }
</style>

<div class="bg-gray-900 min-h-screen p-8 text-gray-300">
    <div class="max-w-xl mx-auto">
        <h1 class="text-4xl font-bold text-center text-blue-500 mb-8">Contact Us</h1>
        
        <form action="contact.php" method="post" class="space-y-6 bg-gray-800 p-8 rounded-xl shadow-lg">
            <div>
                <label for="name" class="block text-gray-400 mb-2">Name</label>
                <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($name); ?>" class="w-full bg-gray-700 text-white p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" readonly>
            </div>
            <div>
                <label for="email" class="block text-gray-400 mb-2">Email</label>
                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>" class="w-full bg-gray-700 text-white p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" readonly>
            </div>
            <div>
                <label for="subject" class="block text-gray-400 mb-2">Subject</label>
                <input type="text" name="subject" id="subject" value="<?php echo htmlspecialchars($subject); ?>" class="w-full bg-gray-700 text-white p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div>
                <label for="message" class="block text-gray-400 mb-2">Message</label>
                <textarea name="message" id="message" rows="6" class="w-full bg-gray-700 text-white p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required><?php echo htmlspecialchars($message_body); ?></textarea>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-blue-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-gray-800">
                Send Message
            </button>
        </form>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-75 hidden">
    <div class="bg-gray-800 p-8 rounded-lg shadow-xl max-w-sm mx-auto text-center">
        <svg class="mx-auto h-16 w-16 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <h3 class="mt-4 text-2xl font-semibold text-white">Message Sent!</h3>
        <p class="mt-2 text-gray-400">Your message has been successfully sent. We'll get back to you soon.</p>
        <button id="closeModal" class="mt-6 w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 transition-colors">Close</button>
    </div>
</div>

<script>
    const successMessageFlag = <?php echo $success_message_flag ? 'true' : 'false'; ?>;
    const successModal = document.getElementById('successModal');
    const closeModalBtn = document.getElementById('closeModal');

    if (successMessageFlag) {
        successModal.classList.remove('hidden');
    

        // Automatically close the modal after 3 seconds
        setTimeout(() => {
            successModal.classList.add('hidden');
        }, 3000);
    }

    closeModalBtn.addEventListener('click', () => {
        successModal.classList.add('hidden');
    });

    // Handle back button on the client-side
    window.addEventListener('popstate', function(event) {
        // Reload the page to ensure fresh content is loaded
        window.location.reload();
    });

</script>

<?php
// Close the main and body tags opened by the header.

require_once 'includes/footer.php';
?>

    </main>
</body>
</html>
