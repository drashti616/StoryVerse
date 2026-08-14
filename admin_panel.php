<?php
// FILE: admin_panel.php
// A tabbed UI for the admin dashboard with client-side filtering.
// Updated to use a POST request to navigate to message_detail.php without showing the ID in the URL.

require_once 'includes/admin_header.php';
require_once 'includes/db_config.php';

// Check if the user is an admin
if ($_SESSION['user_role'] !== 'admin') {
    die("Access denied.");
}

$messages = [];
try {
    // Corrected table username to 'messages' for consistency
    $sql = "SELECT id, username, email, subject, message, sent_at, status FROM messages ORDER BY sent_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $messages = [];
}
?>

<div class="admin-page-inner admin-page-inner--medium">
        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold mb-4 sm:mb-6 text-center text-blue-500">Message Management</h1>

        <!-- Tabbed navigation -->
        <div class="flex flex-wrap sm:flex-nowrap justify-center border-b border-gray-700 mb-4 sm:mb-6 gap-2 sm:gap-4 tab-scroll overflow-x-auto pb-1">
            <button class="tab-button text-sm sm:text-lg px-3 sm:px-4 py-2 hover:text-blue-400 transition-colors whitespace-nowrap shrink-0" data-tab="all">All</button>
            <button class="tab-button text-sm sm:text-lg px-3 sm:px-4 py-2 hover:text-blue-400 transition-colors whitespace-nowrap shrink-0" data-tab="pending">Pending</button>
            <button class="tab-button text-sm sm:text-lg px-3 sm:px-4 py-2 hover:text-blue-400 transition-colors whitespace-nowrap shrink-0" data-tab="approved">Approved</button>
            <button class="tab-button text-sm sm:text-lg px-3 sm:px-4 py-2 hover:text-blue-400 transition-colors whitespace-nowrap shrink-0" data-tab="ignored">Ignored</button>
        </div>
        
        <!-- Message List -->
        <div class="bg-gray-800 p-4 sm:p-6 rounded-lg shadow-xl min-w-0" id="message-list">
            <h2 class="text-xl sm:text-2xl font-semibold mb-4">Messages</h2>

            <?php if (empty($messages)): ?>
                <div class="text-center text-gray-500 py-8">
                    <i class="fas fa-inbox text-5xl mb-4"></i>
                    <p class="text-lg">No messages found.</p>
                </div>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                    <?php
                        // Determine status and color class
                        $status_class = 'border-gray-500';
                        if ($msg['status'] === 'pending') {
                            $status_class = 'border-yellow-500';
                        } elseif ($msg['status'] === 'approved') {
                            $status_class = 'border-green-500';
                        } elseif ($msg['status'] === 'ignored') {
                            $status_class = 'border-red-500';
                        }
                    ?>
                    <!-- A div that acts as a clickable link, triggering the hidden form submission -->
                    <div class="block hover:bg-gray-700 transition-colors rounded-lg mb-2 last:mb-0 animate-fadeIn message-item cursor-pointer" data-status="<?php echo htmlspecialchars($msg['status']); ?>">
                        <!-- Hidden form to submit the message ID -->
                        <form class="message-form" action="message_detail.php" method="post" style="display:none;">
                            <input type="hidden" name="message_id" value="<?php echo htmlspecialchars($msg['id']); ?>">
                        </form>
                        <div class="p-3 sm:p-4 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2 border-l-4 <?php echo $status_class; ?> min-w-0">
                            <!-- Message info on the left -->
                            <div class="flex-grow min-w-0">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 mb-1 min-w-0">
                                    <h3 class="font-semibold text-base sm:text-lg text-white break-anywhere">
                                        <?php echo htmlspecialchars($msg['username']); ?>
                                        <span class="font-normal text-xs sm:text-sm text-gray-400 ml-0 sm:ml-2 block sm:inline break-all">&lt;<?php echo htmlspecialchars($msg['email']); ?>&gt;</span>
                                    </h3>
                                </div>
                                
                                <p class="text-sm text-gray-400 mt-1 break-anywhere">
                                    <span class="font-medium text-white"><?php echo htmlspecialchars($msg['subject'] ?: 'No Subject'); ?></span>
                                    &mdash; <?php echo htmlspecialchars(substr($msg['message'], 0, 80)); ?>...
                                </p>
                            </div>

                            <!-- Date/time on the right -->
                            <div class="flex-shrink-0 text-left sm:text-right text-xs sm:text-sm text-gray-500">
                                <?php echo date("M j, Y", strtotime($msg['sent_at'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
</div>

<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeIn {
        animation: fadeIn 0.5s ease-out forwards;
    }
    .active-tab {
        border-bottom-width: 2px;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const tabButtons = document.querySelectorAll('.tab-button');
        const messageItems = document.querySelectorAll('.message-item');
        const messageList = document.getElementById('message-list');

        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                const tab = button.dataset.tab;

                // Remove active class from all buttons and add to the clicked one
                tabButtons.forEach(btn => {
                    btn.classList.remove('active-tab', 'border-blue-500', 'border-yellow-500', 'border-green-500', 'border-red-500');
                });
                
                // Add the correct active style based on the tab
                if (tab === 'all') {
                    button.classList.add('active-tab', 'border-blue-500');
                } else if (tab === 'pending') {
                    button.classList.add('active-tab', 'border-yellow-500');
                } else if (tab === 'approved') {
                    button.classList.add('active-tab', 'border-green-500');
                } else if (tab === 'ignored') {
                    button.classList.add('active-tab', 'border-red-500');
                }

                // Show/hide messages based on the selected tab
                messageItems.forEach(item => {
                    const itemStatus = item.dataset.status;
                    if (tab === 'all' || itemStatus === tab) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });

        // New: Add click listeners to the message items to submit the hidden form
        messageItems.forEach(item => {
            item.addEventListener('click', () => {
                const form = item.querySelector('.message-form');
                if (form) {
                    form.submit();
                }
            });
        });

        // Initialize the page by clicking the "All" tab
        document.querySelector('[data-tab="all"]').click();
    });
</script>

<?php
require_once 'includes/admin_footer.php';
?>