<?php
// FILE: message_detail.php
// This page now prevents any further status changes once a message is no longer pending.

require_once 'includes/admin_header.php';
require_once 'includes/db_config.php';

// Check if the user is an admin before proceeding
if ($_SESSION['user_role'] !== 'admin') {
    header('Location: admin_panel.php');
    exit;
}

$message = null;
$status_message = '';
$message_id = null;

// Determine the message ID to work with, prioritizing the POST data
if (isset($_POST['message_id'])) {
    $message_id = $_POST['message_id'];
} else {
    // If no message ID is specified in the POST request, die gracefully
    die("Message ID not specified.");
}

// Always fetch the current state of the message from the database
try {
    $sql = "SELECT * FROM messages WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $message_id, PDO::PARAM_INT);
    $stmt->execute();
    $message = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$message) {
        die("Message not found.");
    }

} catch (PDOException $e) {
    error_log("Database error fetching message: " . $e->getMessage());
    die("An error occurred while fetching the message.");
}

// --- Logic to handle form submissions on this page ---
// This block will run when the admin clicks the "Update Message" button
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['status'])) {
    $new_status = $_POST['status'];
    $reply_message = trim($_POST['reply_message'] ?? '');

    // Only allow an update if the message is currently pending
    if ($message['status'] === 'pending') {
        try {
            $sql_update = "UPDATE messages SET status = :status, reply_message = :reply_message WHERE id = :id";
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->bindParam(':status', $new_status, PDO::PARAM_STR);
            $stmt_update->bindParam(':reply_message', $reply_message, PDO::PARAM_STR);
            $stmt_update->bindParam(':id', $message_id, PDO::PARAM_INT);

            if ($stmt_update->execute()) {
                $status_message = "Message status changed successfully!";
                // Re-fetch the message to display the updated data
                $sql_re_fetch = "SELECT * FROM messages WHERE id = :id";
                $stmt_re_fetch = $pdo->prepare($sql_re_fetch);
                $stmt_re_fetch->bindParam(':id', $message_id, PDO::PARAM_INT);
                $stmt_re_fetch->execute();
                $message = $stmt_re_fetch->fetch(PDO::FETCH_ASSOC);
            } else {
                $status_message = "Error updating message. Please try again.";
            }
        } catch (PDOException $e) {
            error_log("Database error updating message: " . $e->getMessage());
            $status_message = "Error updating message. Please try again.";
        }
    }
}
?>

<!-- HTML structure for the message detail page -->
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    body { font-family: 'Inter', sans-serif; }
    .reply-container {
        max-height: 0;
        opacity: 0;
        overflow: hidden;
        transition: max-height 0.5s ease-out, opacity 0.5s ease-out;
    }
    .reply-container.visible {
        max-height: 500px; /* A value large enough to contain the textarea */
        opacity: 1;
    }
</style>

<div class="bg-gray-900 min-h-screen p-8 text-white">
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center mb-6">
            <a href="admin_panel.php" class="text-blue-400 hover:text-blue-300 transition-colors mr-4">
                <i class="fas fa-arrow-left"></i> 
            </a>
            <h1 class="text-4xl font-bold text-blue-500">Message from <?php echo htmlspecialchars($message['username']); ?></h1>
        </div>
        
        <?php if ($status_message): ?>
            <div id="status-alert" class="bg-blue-600 text-white p-4 rounded-lg mb-4">
                <?php echo htmlspecialchars($status_message); ?>
            </div>
        <?php endif; ?>

        <div class="bg-gray-800 p-6 rounded-lg shadow-xl mb-8">
            <div class="space-y-4">
                <p><span class="font-bold">Email:</span> <?php echo htmlspecialchars($message['email']); ?></p>
                <p><span class="font-bold">Subject:</span> <?php echo htmlspecialchars($message['subject'] ?: 'No Subject'); ?></p>
                <p><span class="font-bold">Message:</span></p>
                <div class="bg-gray-700 p-4 rounded-lg">
                    <p class="whitespace-pre-wrap"><?php echo htmlspecialchars($message['message']); ?></p>
                </div>
                <p class="text-sm text-gray-400 text-right">Sent on: <?php echo date("F j, Y, g:i a", strtotime($message['sent_at'])); ?></p>
            </div>
            
            <hr class="border-gray-700 my-6">
            
            <?php if ($message['status'] === 'pending'): ?>
                <!-- This block is only shown if the message is still pending -->
                <form action="message_detail.php" method="post" class="space-y-4">
                    <input type="hidden" name="message_id" value="<?php echo htmlspecialchars($message['id']); ?>">
                    
                    <div>
                        <label for="status" class="block text-gray-400 mb-2">Message Status</label>
                        <select name="status" id="status" class="w-full bg-gray-700 text-white p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="pending" <?php echo ($message['status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="approved" <?php echo ($message['status'] === 'approved') ? 'selected' : ''; ?>>Approved</option>
                            <option value="ignored" <?php echo ($message['status'] === 'ignored') ? 'selected' : ''; ?>>Ignored</option>
                        </select>
                    </div>

                    <!-- Reply field, shown only when status is 'approved' -->
                    <div id="reply-container" class="reply-container">
                        <label for="reply_message" class="block text-gray-400 mb-2">Reply Message</label>
                        <textarea name="reply_message" id="reply_message" rows="10" class="w-full bg-gray-700 text-white p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Type your reply here..."><?php echo htmlspecialchars($message['reply_message'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-blue-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-gray-800">
                        Update Message
                    </button>
                </form>
            <?php else: ?>
                <!-- This block is shown once the status is no longer pending -->
                <div class="text-center bg-gray-700 p-4 rounded-lg">
                    <span class="text-lg font-semibold text-gray-300">Status: </span>
                    <span class="text-lg font-bold
                        <?php
                            if ($message['status'] === 'approved') {
                                echo 'text-green-400';
                            } elseif ($message['status'] === 'ignored') {
                                echo 'text-red-400';
                            }
                        ?>">
                        <?php echo ucfirst($message['status']); ?>
                    </span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const statusSelect = document.getElementById('status');
    const replyContainer = document.getElementById('reply-container');
    const replyTextarea = document.getElementById('reply_message');
    const statusAlert = document.getElementById('status-alert');

    // Function to toggle the reply field visibility
    const toggleReplyField = () => {
        if (statusSelect && replyContainer && replyTextarea) {
            if (statusSelect.value === 'approved') {
                replyContainer.style.maxHeight = '500px';
                replyContainer.style.opacity = '1';
            } else {
                replyContainer.style.maxHeight = '0';
                replyContainer.style.opacity = '0';
                // Clear the reply message when switching away from 'approved'
                replyTextarea.value = '';
            }
        }
    };

    // Add event listener to the status dropdown
    if (statusSelect) {
        statusSelect.addEventListener('change', toggleReplyField);
        // Initial state setup on page load
        toggleReplyField();
    }
    
    // Automatically hide the status alert after a few seconds
    if (statusAlert) {
        setTimeout(() => {
            statusAlert.style.transition = 'opacity 0.5s ease-out';
            statusAlert.style.opacity = '0';
            setTimeout(() => {
                statusAlert.remove();
            }, 500); // Wait for the fade-out to finish before removing
        }, 3000); // Hide after 3 seconds
    }
});
</script>

<?php
require_once 'includes/admin_footer.php';
?>