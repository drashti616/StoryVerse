<?php
// FILE: manage_users.php
// This page is for the admin to view and manage user accounts and subscriptions.

require_once 'includes/admin_header.php';
require_once 'includes/db_config.php';

// Check if the user is an admin before proceeding
if ($_SESSION['user_role'] !== 'admin') {
    header('Location: admin_panel.php');
    exit;
}

$status_message = '';
$users = [];

// --- Fetch all users and their subscription data ---
try {
    $sql = "SELECT id, username, email, created_at, profile_image_path, is_deleted, deleted_at FROM users 
            WHERE role != 'admin' 
            ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $status_message = "Error fetching user data.";
    $users = [];
}
?>

<div class="admin-page-inner admin-page-inner--wide">
        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-blue-500 mb-4 sm:mb-6 text-center">Manage Users</h1>
        <?php if ($status_message): ?>
            <div id="status-alert" class="bg-red-500 text-white p-4 rounded-lg mb-4 text-center">
                <?php echo htmlspecialchars($status_message); ?>
            </div>
        <?php endif; ?>

        <!-- LOGOUT CONFIRMATION MODAL -->
        <div id="confirm-delete-modal" class="fixed inset-0 hidden items-center justify-center modal-overlay z-[9999] px-4">
            <div class="bg-gray-800 p-4 sm:p-6 rounded-lg shadow-2xl max-w-sm w-full modal-safe transform transition-all duration-300 scale-95 opacity-0">
                <h3 class="text-xl font-bold text-red-500 mb-4 flex items-center">
                    <i class="fas fa-exclamation-triangle mr-3"></i> Confirm Deletion
                </h3>
                <p class="text-gray-300 mb-6">Are you sure you want to permanently remove this user? This action is irreversible.</p>
            <div class="flex flex-col-reverse sm:flex-row justify-end gap-3">
                <button id="cancel-delete-btn" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-500 transition-colors w-full sm:w-auto">
                    Cancel
                </button>
                <a href="#" id="confirm-delete-btn" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors text-center w-full sm:w-auto">
                    Yes, Remove User
                </a>
            </div>
            </div>
        </div>
        <!-- END LOGOUT CONFIRMATION MODAL -->

        <!-- Users and Subscription List -->
        <div class="bg-gray-800 p-4 sm:p-6 lg:p-8 rounded-xl shadow-lg min-w-0">
            <?php if (empty($users)): ?>
                <p class="text-gray-400 text-center">No users found.</p>
            <?php else: ?>
                <div class="table-wrapper">
                    <table class="min-w-full text-left table-auto responsive-table w-full">
                        <thead>
                            <tr class="bg-gray-700 text-gray-300">
                                <th class="p-3 sm:p-4 rounded-tl-lg">Profile</th>
                                <th class="p-3 sm:p-4">Username</th>
                                <th class="p-3 sm:p-4">Email</th>
                                <th class="p-3 sm:p-4 hidden lg:table-cell">Member Since</th>
                                <th class="p-3 sm:p-4 rounded-tr-lg text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr class="border-t border-gray-700">
                                    <td class="p-3 sm:p-4" data-label="Profile">
                                        <div class="w-10 h-10 rounded-full border border-gray-600 overflow-hidden bg-gray-700 flex items-center justify-center">
                                            <?php if (!empty($user['profile_image_path'])): ?>
                                                <img src="<?php echo htmlspecialchars($user['profile_image_path']) . '?' . time(); ?>" alt="Profile Photo" class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <!-- Inline SVG default avatar (gray man) -->
                                                <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                                </svg>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    
                                    <!-- Cleaned up Username Column -->
                                    <td class="p-3 sm:p-4 font-semibold break-anywhere" data-label="Username">
                                        <?php echo htmlspecialchars($user['username']); ?>
                                    </td>
                                    
                                    <td class="p-3 sm:p-4 text-gray-400 break-all" data-label="Email"><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td class="p-3 sm:p-4 text-gray-400 hidden lg:table-cell" data-label="Member Since"><?php echo date('F j, Y, g:i a', strtotime($user['created_at'])); ?></td>
                                    
                                    <!-- Actions Column with the Badge -->
                                    <td class="p-3 sm:p-4 text-right" data-label="Actions">
                                        <?php if (!empty($user['is_deleted'])): ?>
                                            <span class="inline-flex items-center px-3 py-1 text-xs font-bold uppercase tracking-wider rounded-full bg-red-600 text-white shadow-sm">
                                                Deleted
                                            </span>
                                        <?php else: ?>
                                            <button 
                                                class="delete-user-btn text-red-500 hover:text-red-400 w-full sm:w-auto text-sm sm:text-base"
                                                data-user-id="<?php echo htmlspecialchars($user['id']); ?>">
                                                <i class="fas fa-trash-alt"></i> Remove
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('confirm-delete-modal');
        const modalContent = modal.querySelector('div');
        const confirmBtn = document.getElementById('confirm-delete-btn');
        const cancelBtn = document.getElementById('cancel-delete-btn');
        const deleteButtons = document.querySelectorAll('.delete-user-btn');

        let userIdToDelete = null;

        deleteButtons.forEach(button => {
            button.addEventListener('click', (event) => {
                userIdToDelete = event.currentTarget.dataset.userId;
                openModal();
            });
        });

        confirmBtn.addEventListener('click', () => {
            if (userIdToDelete) {
                // Redirect to the server-side deletion script with the user ID
                window.location.href = `admin_delete_user.php?id=${userIdToDelete}`;
            }
        });

        cancelBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal();
            }
        });

        function openModal() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                modalContent.classList.remove('opacity-0', 'scale-95');
                modalContent.classList.add('opacity-100', 'scale-100');
            }, 10);
        }

        function closeModal() {
            modalContent.classList.remove('opacity-100', 'scale-100');
            modalContent.classList.add('opacity-0', 'scale-95');
            setTimeout(() => {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
            }, 300);
        }
    });
</script>

<?php
require_once 'includes/admin_footer.php';
?>
