<?php
// FILE: existing_audiobooks.php
// This is the admin-facing page to display existing audiobooks.

require_once 'includes/admin_header.php';
require_once 'includes/db_config.php';

// Check if the user is an admin before proceeding
if ($_SESSION['user_role'] !== 'admin') {
    header('Location: admin_panel.php');
    exit;
}

$status_message = '';
$audiobooks = [];

// --- Handle Form Submissions (Delete) ---

// Handle Delete Audiobook submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'delete_book') {
    $book_id = $_POST['book_id'] ?? null;
    if ($book_id) {
        try {
            $pdo->beginTransaction();

            // First, get file paths to delete files from the server
            $sql_get_paths = "SELECT cover_image_path FROM books WHERE id = :id";
            $stmt_get_paths = $pdo->prepare($sql_get_paths);
            $stmt_get_paths->bindParam(':id', $book_id, PDO::PARAM_INT);
            $stmt_get_paths->execute();
            $paths = $stmt_get_paths->fetch(PDO::FETCH_ASSOC);

            // Get all audio paths for the parts to delete files from server
            $sql_get_audio_paths = "SELECT audio_path FROM book_parts WHERE book_id = :id";
            $stmt_get_audio_paths = $pdo->prepare($sql_get_audio_paths);
            $stmt_get_audio_paths->bindParam(':id', $book_id, PDO::PARAM_INT);
            $stmt_get_audio_paths->execute();
            $audio_paths = $stmt_get_audio_paths->fetchAll(PDO::FETCH_ASSOC);

            // Delete files from the server (cover image and all audio parts)
            if ($paths && $paths['cover_image_path'] && file_exists($paths['cover_image_path'])) {
                unlink($paths['cover_image_path']);
            }

            foreach($audio_paths as $audio) {
                if($audio['audio_path'] && file_exists($audio['audio_path'])) {
                    unlink($audio['audio_path']);
                }
            }

            // Then, delete the database record. ON DELETE CASCADE will handle book_parts.
            $sql = "DELETE FROM books WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $book_id, PDO::PARAM_INT);
            $stmt->execute();

            $pdo->commit();
            $status_message = "Audiobook and all related files deleted successfully!";
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Database error: " . $e->getMessage());
            $status_message = "Error deleting audiobook. Please try again.";
        }
    }
}

// --- Fetch all audiobooks for display ---
try {
    $sql = "SELECT id, title, author, genre, description, cover_image_path FROM books ORDER BY title ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $audiobooks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $status_message = "Error fetching audiobooks.";
    $audiobooks = [];
}
?>

<div class="admin-page-inner admin-page-inner--wide">
        <div class="mb-4 sm:mb-6">
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-blue-500 text-center">Existing Audiobooks</h1>
        </div>

        <div class="mb-6 sm:mb-8">
            <input type="text" id="searchInput" placeholder="Search by title or author" class="w-full p-3 sm:p-4 rounded-lg bg-gray-800 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <?php if ($status_message): ?>
            <div id="status-alert" class="bg-blue-600 text-white p-4 rounded-lg mb-4 text-center">
                <?php echo $status_message; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($audiobooks)): ?>
            <p class="text-lg text-center text-gray-400 mt-12">No audiobooks are currently available.</p>
        <?php else: ?>
            <div id="audiobook-grid" class="admin-audiobook-grid">
                <?php foreach ($audiobooks as $book): ?>
                    <div class="audiobook-card bg-gray-800 p-4 rounded-xl shadow-2xl flex flex-col items-center text-center transition-transform duration-300 hover-scale-card relative h-full min-w-0 w-full"
                         data-title="<?php echo htmlspecialchars($book['title']); ?>"
                         data-author="<?php echo htmlspecialchars($book['author']); ?>">
                        <img src="<?php echo $book['cover_image_path'] ?? 'https://placehold.co/200x200/1f2937/d1d5db?text=No+Cover'; ?>"
                             alt="<?php echo htmlspecialchars($book['title']); ?> Cover"
                             class="w-full max-w-[12rem] aspect-square rounded-lg object-cover mb-4 shadow-lg"
                             onerror="this.onerror=null;this.src='https://placehold.co/200x200/1f2937/d1d5db?text=No+Cover';">

                        <div class="flex flex-col flex-grow items-center">
                            <h3 class="text-base sm:text-lg font-bold text-white mt-2 break-anywhere w-full"><?php echo htmlspecialchars_decode($book['title']); ?></h3>
                            <p class="text-gray-400 text-sm break-anywhere w-full">by <?php echo htmlspecialchars_decode($book['author']); ?></p>
                        </div>
                        <div class="mt-auto w-full flex flex-col sm:flex-row justify-center gap-2 pt-4">
                            <a href="admin_book_parts.php?book_id=<?php echo $book['id']; ?>" class="inline-block bg-blue-600 text-white py-2 px-4 rounded-full hover:bg-blue-700 transition-colors font-semibold text-center w-full sm:w-auto">
                                View
                            </a>
                            <button class="delete-btn bg-red-600 text-white py-2 px-4 rounded-full hover:bg-red-700 transition-colors font-semibold text-sm w-full sm:w-auto"
                                    data-id="<?php echo $book['id']; ?>"
                                    data-title="<?php echo htmlspecialchars_decode($book['title']); ?>">
                                    <i class="fas fa-trash-alt"></i> Delete
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
</div>

<style>
    .fade-out {
        opacity: 0;
        transition: opacity 0.5s ease-out;
    }
</style>

<div id="deleteModal" class="sv-modal">
    <div class="sv-modal-content">
        <h2 class="text-xl sm:text-2xl font-semibold text-white mb-4">Confirm Deletion</h2>
        <p class="text-gray-400 mb-6 break-anywhere">Are you sure you want to delete "<span id="deleteBookTitle" class="font-bold text-white"></span>"? This will also delete all associated parts and files.</p>
        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 sm:gap-4">
            <button id="cancelDeleteBtn" class="bg-gray-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-gray-700 transition-colors">
                Cancel
            </button>
            <form id="deleteForm" action="existing_audiobooks.php" method="post" class="w-full sm:w-auto">
                <input type="hidden" name="action" value="delete_book">
                <input type="hidden" name="book_id" id="delete-book-id">
                <button type="submit" class="bg-red-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-red-700 transition-colors w-full">
                    Delete
                </button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Timed status alert
    const statusAlert = document.getElementById('status-alert');
    if (statusAlert) {
        setTimeout(() => {
            statusAlert.classList.add('fade-out');
            setTimeout(() => {
                statusAlert.remove();
            }, 500);
        }, 3000);
    }

    // Modal functionality for Delete
    const deleteModal = document.getElementById('deleteModal');
    const deleteButtons = document.querySelectorAll('.delete-btn');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');

    deleteButtons.forEach(button => {
        button.addEventListener('click', (event) => {
            event.stopPropagation(); // Prevent card from toggling
            const bookTitle = button.dataset.title;
            const bookId = button.dataset.id;

            document.getElementById('deleteBookTitle').textContent = bookTitle;
            document.getElementById('delete-book-id').value = bookId;

            deleteModal.style.display = 'flex';
        });
    });

    cancelDeleteBtn.addEventListener('click', () => {
        deleteModal.style.display = 'none';
    });

    window.addEventListener('click', (event) => {
        if (event.target === deleteModal) {
            deleteModal.style.display = 'none';
        }
    });

    // --- Search functionality from browse.php ---
    const searchInput = document.getElementById('searchInput');
    const audiobookCards = document.querySelectorAll('.audiobook-card');

    searchInput.addEventListener('input', () => {
        const searchTerm = searchInput.value.toLowerCase();

        audiobookCards.forEach(card => {
            const title = card.getAttribute('data-title').toLowerCase();
            const author = card.getAttribute('data-author').toLowerCase();

            if (title.includes(searchTerm) || author.includes(searchTerm)) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    });
});
</script>

<?php
require_once 'includes/admin_footer.php';
?>

