<?php
// FILE: user_messages.php
// This page displays a user's past messages and the admin's replies.


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

$user_id = $_SESSION['user_id'];
$messages = [];

// Fetch past messages from the current user, ordered by most recent
try {
    $sql_fetch = "SELECT * FROM messages WHERE user_id = :user_id ORDER BY sent_at DESC";
    $stmt_fetch = $pdo->prepare($sql_fetch);
    $stmt_fetch->bindParam(":user_id", $user_id, PDO::PARAM_INT);
    $stmt_fetch->execute();
    $messages = $stmt_fetch->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error fetching user messages: " . $e->getMessage());
    $messages = [];
}
?>

<div class="w-full min-w-0">
    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-blue-500 text-center mb-4 sm:mb-6">Your Messages</h1>

    <!-- Display user messages -->
    <?php if (empty($messages)): ?>
        <div class="bg-gray-700 text-gray-400 p-6 rounded-lg text-center max-w-xl mx-auto">
            <i class="fas fa-inbox text-5xl mb-4"></i>
            <p class="text-xl font-semibold">You haven't sent any messages yet.</p>
            <p class="mt-2">Go to the <a href="contact.php" class="text-blue-400 hover:text-blue-300">Contact Us</a> page to get started.</p>
        </div>
    <?php else: ?>
        <div class="space-y-4 sm:space-y-6 max-w-2xl mx-auto">
            <?php foreach ($messages as $msg): ?>
                <!-- Message container -->
                <div class="flex flex-col space-y-2 min-w-0">
                    <!-- User's Message Bubble -->
                    <div class="flex justify-start min-w-0">
                        <div class="bg-blue-600 text-white p-3 sm:p-4 rounded-xl shadow-lg w-full max-w-[95%] sm:max-w-[80%] min-w-0">
                                                        <!-- Updated Flex container for Subject and Badge -->
                            <div class="flex flex-row flex-wrap justify-between items-start gap-3 mb-2 min-w-0">
                                <h3 class="font-bold break-anywhere flex-1 mt-0.5"><?php echo htmlspecialchars($msg['subject'] ?: 'No Subject'); ?></h3>
                                <?php
                                    // Determine the status text and color
                                    $status_class = 'bg-gray-500';
                                    $status_text = 'Pending';
                                    if ($msg['status'] === 'approved') {
                                        $status_class = 'bg-green-500';
                                        $status_text = 'Approved';
                                    } elseif ($msg['status'] === 'ignored') {
                                        $status_class = 'bg-red-500';
                                        $status_text = 'Ignored';
                                    }
                                ?>
                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold uppercase tracking-wider rounded-full <?php echo $status_class; ?> text-white shrink-0 whitespace-nowrap shadow-sm">
                                    <?php echo $status_text; ?>
                                </span>
                            </div>
                            <p class="text-sm break-anywhere"><?php echo htmlspecialchars($msg['message']); ?></p>
                            <div class="text-right text-xs text-blue-200 mt-2">
                                <?php echo date("F j, Y, g:i a", strtotime($msg['sent_at'])); ?>
                            </div>
                        </div>
                    </div>

                    <!-- Admin's Reply Bubble (if available) -->
                    <?php if ($msg['status'] === 'approved' && !empty($msg['reply_message'])): ?>
                        <div class="flex justify-end min-w-0">
                            <div class="bg-gray-700 text-gray-200 p-3 sm:p-4 rounded-xl shadow-lg w-full max-w-[95%] sm:max-w-[80%] min-w-0">
                                <p class="font-bold text-white mb-1">Admin Reply</p>
                                <p class="text-sm whitespace-pre-wrap leading-relaxed break-anywhere"><?php echo htmlspecialchars($msg['reply_message']); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
// Close the main and body tags opened by the header.
require_once 'includes/footer.php';
?>
