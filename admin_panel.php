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

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
    body {
        font-family: 'Inter', sans-serif;
    }
    /* Simple fade-in animation for message list */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeIn {
        animation: fadeIn 0.5s ease-out forwards;
    }
    /* Style for the active tab */
    .active-tab {
        border-bottom-width: 2px;
    }
</style>

<div class="bg-gray-900 min-h-screen p-8 text-white">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-4xl font-bold mb-6 text-center text-blue-500">Message Management</h1>

        <!-- Tabbed navigation -->
        <div class="flex justify-center border-b border-gray-700 mb-6 space-x-4">
            <button class="tab-button text-lg px-4 py-2 hover:text-blue-400 transition-colors" data-tab="all">All</button>
            <button class="tab-button text-lg px-4 py-2 hover:text-blue-400 transition-colors" data-tab="pending">Pending</button>
            <button class="tab-button text-lg px-4 py-2 hover:text-blue-400 transition-colors" data-tab="approved">Approved</button>
            <button class="tab-button text-lg px-4 py-2 hover:text-blue-400 transition-colors" data-tab="ignored">Ignored</button>
        </div>
        
        <!-- Message List -->
        <div class="bg-gray-800 p-6 rounded-lg shadow-xl" id="message-list">
            <h2 class="text-2xl font-semibold mb-4">Messages</h2>

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
                        <div class="p-4 flex items-start justify-between border-l-4 <?php echo $status_class; ?>">
                            <!-- Message info on the left -->
                            <div class="flex-grow min-w-0 pr-4">
                                <div class="flex items-center justify-between mb-1">
                                    <h3 class="font-semibold text-lg text-white">
                                        <?php echo htmlspecialchars($msg['username']); ?>
                                        <span class="font-normal text-sm text-gray-400 ml-2">&lt;<?php echo htmlspecialchars($msg['email']); ?>&gt;</span>
                                    </h3>
                                </div>
                                
                                <p class="text-sm text-gray-400 mt-1 truncate">
                                    <span class="font-medium text-white"><?php echo htmlspecialchars($msg['subject'] ?: 'No Subject'); ?></span>
                                    &mdash; <?php echo htmlspecialchars(substr($msg['message'], 0, 80)); ?>...
                                </p>
                            </div>

                            <!-- Date/time on the right -->
                            <div class="flex-shrink-0 text-right text-sm text-gray-500 mt-1">
                                <?php echo date("M j, Y", strtotime($msg['sent_at'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

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