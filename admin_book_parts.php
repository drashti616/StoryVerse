<?php
// FILE: admin_book_parts.php
// This is the admin-facing page to display and manage parts of a specific audiobook.

// --- ALL LOGIC AND REDIRECTS MUST BE AT THE VERY TOP OF THE FILE. ---

// Include database config first
require_once 'includes/db_config.php';

// Check if a form was submitted before including the header.
// This ensures that any redirects will work properly.

$status_message = '';
$book_id = $_GET['book_id'] ?? null;

// --- Handle Form Submissions (Delete Part) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'delete_part') {
    $part_id = $_POST['part_id'] ?? null;
    $book_id_post = $_POST['book_id'] ?? null;

    if ($part_id && $book_id_post) {
        try {
            $pdo->beginTransaction();

            // First, get the audio file path to delete it from the server
            $sql_get_path = "SELECT audio_path FROM book_parts WHERE id = :id";
            $stmt_get_path = $pdo->prepare($sql_get_path);
            $stmt_get_path->bindParam(':id', $part_id, PDO::PARAM_INT);
            $stmt_get_path->execute();
            $path = $stmt_get_path->fetch(PDO::FETCH_ASSOC);
            
            // Define the destination for file storage with absolute paths
            $new_upload_dir = 'C:/xampp/htdocs/Drashti/audiobook_website/saved_audio/';
            $audio_file_path = str_replace('saved_audio/', $new_upload_dir, $path['audio_path']);

            // Delete file from the server
            if ($path && $path['audio_path'] && file_exists($audio_file_path)) {
                unlink($audio_file_path);
            }

            // Then, delete the database record
            $sql = "DELETE FROM book_parts WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $part_id, PDO::PARAM_INT);
            $stmt->execute();

            $pdo->commit();
            $status_message = "Audio part deleted successfully!";
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Database error: " . $e->getMessage());
            // Added the detailed error message for better debugging
            $status_message = "Error deleting audio part. Please try again. Error: " . $e->getMessage();
        }
    }
    // Redirect back to avoid resubmission on refresh
    header("Location: admin_book_parts.php?book_id=" . $book_id_post . "&status=" . urlencode($status_message));
    exit;
}

// --- Handle Form Submissions (Add Part) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'add_part') {
    $book_id_post = $_POST['book_id'] ?? null;
    $audio_file_path = null;
    $part_number = $_POST['part_number'] ?? null;

    // Validation
    if (!$book_id_post || !is_numeric($book_id_post) || !isset($_FILES['audio_file']) || empty($part_number) || !is_numeric($part_number)) {
        $status_message = "Invalid book ID or part number, or no file uploaded.";
    } else {
        try {
            // Define the new destination for file storage
            // IMPORTANT: The path you provide must be writable by the web server user.
            $new_upload_dir = 'C:/xampp/htdocs/Drashti/audiobook_website/saved_audio/';
            
            // Create a book-specific subdirectory if it doesn't exist
            $book_upload_dir = $new_upload_dir . 'book_' . $book_id_post . '/';
            if (!is_dir($book_upload_dir)) {
                mkdir($book_upload_dir, 0777, true);
            }
            
            // Create a unique filename for the part
            $file_extension = pathinfo($_FILES['audio_file']['name'], PATHINFO_EXTENSION);
            $unique_filename = 'part_' . $part_number . '.' . $file_extension;
            $audio_file_path = $book_upload_dir . $unique_filename;

            if (move_uploaded_file($_FILES['audio_file']['tmp_name'], $audio_file_path)) {
                // The path stored in the database should be relative to the web root
                $db_audio_path = 'saved_audio/book_' . $book_id_post . '/' . $unique_filename;
                
                // Insert the new book part
                $sql_part = "INSERT INTO book_parts (book_id, part_number, audio_path) VALUES (:book_id, :part_number, :audio_path)";
                $stmt_part = $pdo->prepare($sql_part);
                $stmt_part->bindParam(':book_id', $book_id_post, PDO::PARAM_INT);
                $stmt_part->bindParam(':part_number', $part_number, PDO::PARAM_INT);
                $stmt_part->bindParam(':audio_path', $db_audio_path);
                $stmt_part->execute();

                $status_message = "New part added successfully!";
            } else {
                $status_message = "Error uploading file. Please check file permissions.";
            }
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            $status_message = "Error adding new part: " . $e->getMessage();
        }
    }
    // Redirect back to avoid resubmission on refresh
    header("Location: admin_book_parts.php?book_id=" . $book_id_post . "&status=" . urlencode($status_message));
    exit;
}

// Now include the header, as all redirect logic is complete
require_once 'includes/admin_header.php';

$book_parts = [];
$book_title = '';

// Check if the user is an admin before proceeding
if ($_SESSION['user_role'] !== 'admin') {
    header('Location: admin_panel.php');
    exit;
}

// --- Fetch book and its parts for display ---
if ($book_id) {
    try {
        // Get book title
        $sql_book_title = "SELECT title, author, genre, description, cover_image_path FROM books WHERE id = :id";
        $stmt_book_title = $pdo->prepare($sql_book_title);
        $stmt_book_title->bindParam(':id', $book_id, PDO::PARAM_INT);
        $stmt_book_title->execute();
        $book_info = $stmt_book_title->fetch(PDO::FETCH_ASSOC);
        
        if ($book_info) {
            $book_title = $book_info['title'];
            $book_author = $book_info['author'];
            $book_genre = $book_info['genre'];
            $book_description = $book_info['description'];
            $book_cover_path = $book_info['cover_image_path'];
        }

        // Get book parts
        $sql_parts = "SELECT id, part_number, audio_path FROM book_parts WHERE book_id = :id ORDER BY part_number ASC";
        $stmt_parts = $pdo->prepare($sql_parts);
        $stmt_parts->bindParam(':id', $book_id, PDO::PARAM_INT);
        $stmt_parts->execute();
        $book_parts = $stmt_parts->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Log the full error message for better debugging
        error_log("Database error: " . $e->getMessage());
        $status_message = "Error fetching book details. " . $e->getMessage();
        $book_parts = [];
    }
}

// Check for status message from URL redirection
if (isset($_GET['status'])) {
    $status_message = $_GET['status'];
}

?>

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    body { font-family: 'Inter', sans-serif; }
    .fade-out {
        opacity: 0;
        transition: opacity 0.5s ease-out;
    }
    .modal {
        display: none;
        position: fixed;
        z-index: 100;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.7);
        justify-content: center;
        align-items: center;
    }
    .modal-content {
        background-color: #1f2937;
        padding: 2rem;
        border-radius: 0.75rem;
        width: 90%;
        max-width: 500px;
        position: relative;
        max-height: 96vh;
        overflow-y: auto;
    }
</style>

<div class="bg-gray-900 min-h-screen p-8 text-white">
    <div class="max-w-6xl mx-auto">
        <div class="flex items-center mb-6">
            <a href="existing_audiobooks.php" class="text-blue-400 hover:text-blue-300 transition-colors mr-4">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-4xl font-bold text-blue-500 flex-grow">Manage Parts for: <?php echo $book_title; ?></h1>
            <button id="addPartBtn" class="bg-blue-600 text-white py-2 px-4 rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                <i class="fas fa-plus mr-2"></i>Add Part
            </button>
        </div>
        
        <?php if ($status_message): ?>
            <div id="status-alert" class="bg-blue-600 text-white p-4 rounded-lg mb-4 text-center">
                <?php echo $status_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($book_id && $book_title): ?>
        <div class="bg-gray-800 p-6 rounded-xl shadow-lg mb-6 flex flex-col md:flex-row items-center">
            <img src="<?php echo $book_cover_path ?? 'https://placehold.co/150x225/111827/9CA3AF?text=No+Cover'; ?>" 
                 alt="Cover of <?php echo $book_title; ?>" 
                 class="w-36 h-auto object-cover rounded-lg mb-4 md:mb-0 md:mr-6 shadow-md">
            <div class="text-center md:text-left">
                <h2 class="text-2xl font-bold text-white"><?php echo $book_title; ?></h2>
                <p class="text-md text-gray-400 font-medium">by <?php echo $book_author; ?></p>
                <p class="text-sm text-gray-300 mt-2">Genre: <span class="font-semibold"><?php echo $book_genre; ?></span></p>
                <p class="text-sm text-gray-300 mt-2"><?php echo nl2br($book_description); ?></p>
            </div>
        </div>
        <?php endif; ?>

        <div class="bg-gray-800 p-6 rounded-xl shadow-lg">
            <h2 class="text-2xl font-semibold text-white mb-6">Audio Parts</h2>
            <?php if (empty($book_parts)): ?>
                <p class="text-gray-400 text-center py-8">No parts found for this book. Use the "Add Part" button to get started.</p>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($book_parts as $part): ?>
                        <div class="bg-gray-700 rounded-xl overflow-hidden shadow-lg p-6">
                            <h3 class="text-lg font-bold text-white mb-2">Part <?php echo $part['part_number']; ?></h3>
                            <audio controls class="w-full mt-4 rounded-md">
                                <source src="<?php echo $part['audio_path']; ?>" type="audio/mpeg">
                                Your browser does not support the audio element.
                            </audio>
                            <div class="mt-4 text-right">
                                <button class="delete-btn bg-red-600 text-white py-2 px-4 rounded-lg hover:bg-red-700 transition-colors text-sm font-semibold"
                                        data-part-id="<?php echo $part['id']; ?>"
                                        data-book-id="<?php echo $book_id; ?>"
                                        data-part-title="Part <?php echo $part['part_number']; ?>">
                                        <i class="fas fa-trash-alt"></i> Delete Part
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="addPartModal" class="modal">
    <div class="modal-content">
        <h2 class="text-2xl font-semibold text-white mb-4">Add a New Part to <?php echo $book_title; ?></h2>
        <form action="admin_book_parts.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add_part">
            <input type="hidden" name="book_id" value="<?php echo $book_id; ?>">

            <div class="mb-6">
                <label for="part_number" class="block text-gray-300 font-semibold mb-2">Part Number</label>
                <input type="number" id="part_number" name="part_number" required
                       class="w-full px-4 py-2 rounded-lg bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
            </div>
            
            <div class="mb-6">
                <label for="audio_file" class="block text-gray-300 font-semibold mb-2">Audio File (MP3)</label>
                <input type="file" id="audio_file" name="audio_file" required accept=".mp3"
                       class="w-full text-white bg-gray-700 rounded-lg p-2 file:mr-4 file:py-2 file:px-4
                              file:rounded-full file:border-0 file:text-sm file:font-semibold
                              file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            </div>

            <div class="flex justify-end space-x-4">
                <button type="button" id="cancelAddBtn" class="bg-gray-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-gray-700 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="bg-blue-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                    Add Part
                </button>
            </div>
        </form>
    </div>
</div>

<div id="deleteModal" class="modal">
    <div class="modal-content p-8">
        <h2 class="text-2xl font-semibold text-white mb-4">Confirm Deletion</h2>
        <p class="text-gray-400 mb-6">Are you sure you want to delete "<span id="deletePartTitle" class="font-bold text-white"></span>"?</p>
        <div class="flex justify-end space-x-4">
            <button type="button" id="cancelDeleteBtn" class="bg-gray-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-gray-700 transition-colors">
                    Cancel
                </button>
                <form id="deleteForm" action="admin_book_parts.php" method="post" class="inline-block">
                    <input type="hidden" name="action" value="delete_part">
                    <input type="hidden" name="part_id" id="delete-part-id">
                    <input type="hidden" name="book_id" id="delete-book-id">
                    <button type="submit" class="bg-red-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-red-700 transition-colors">
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
            const partTitle = button.dataset.partTitle;
            const partId = button.dataset.partId;
            const bookId = button.dataset.bookId;

            document.getElementById('deletePartTitle').textContent = partTitle;
            document.getElementById('delete-part-id').value = partId;
            document.getElementById('delete-book-id').value = bookId;

            deleteModal.style.display = 'flex';
        });
    });

    cancelDeleteBtn.addEventListener('click', () => {
        deleteModal.style.display = 'none';
    });

    // Modal functionality for Add Part
    const addPartModal = document.getElementById('addPartModal');
    const addPartBtn = document.getElementById('addPartBtn');
    const cancelAddBtn = document.getElementById('cancelAddBtn');

    addPartBtn.addEventListener('click', () => {
        addPartModal.style.display = 'flex';
    });

    cancelAddBtn.addEventListener('click', () => {
        addPartModal.style.display = 'none';
    });

    window.addEventListener('click', (event) => {
        if (event.target === deleteModal) {
            deleteModal.style.display = 'none';
        }
        if (event.target === addPartModal) {
            addPartModal.style.display = 'none';
        }
    });

    // --- New code for single audio playback ---
    const allAudioPlayers = document.querySelectorAll('audio');

    allAudioPlayers.forEach(player => {
        player.addEventListener('play', () => {
            allAudioPlayers.forEach(otherPlayer => {
                // If this is not the current player, pause it.
                if (otherPlayer !== player) {
                    otherPlayer.pause();
                }
            });
        });
    });
});
</script>
<?php
require_once 'includes/admin_footer.php';
?>