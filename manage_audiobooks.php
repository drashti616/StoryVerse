<?php
// FILE: manage_audiobooks.php
// This is the admin-facing page for adding new audiobooks.

// --- ALL LOGIC AND REDIRECTS MUST BE AT THE VERY TOP OF THE FILE. ---

// Include database config first
require_once 'includes/db_config.php';


$status_message = '';
$is_editing_existing = false;
$book_id = $_GET['book_id'] ?? null;
$book_title = '';

// Check if a form was submitted before including the header.
// This ensures that any redirects will work properly.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_book') {
        // Handle New Book Submission
        $title = $_POST['title'] ?? '';
        $author = $_POST['author'] ?? '';
        $genre = $_POST['genre'] ?? '';
        $description = $_POST['description'] ?? '';

        // Validation for new book
        if (empty($title) || empty($author) || empty($genre) || empty($description) || !isset($_FILES['cover_image'])) {
            $status_message = "Please fill in all fields and upload the cover image.";
        } else {
            try {
                // Define the new destination for file storage with absolute paths
                $image_upload_dir = 'C:/xampp/htdocs/Drashti/audiobook_website/saved_image/';
                
                // Create the necessary directories if they don't exist
                if (!is_dir($image_upload_dir)) {
                    mkdir($image_upload_dir, 0777, true);
                }
                
                // Create unique filename to avoid conflicts
                $image_extension = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
                $unique_image_filename = 'cover_' . time() . '.' . $image_extension;
                $cover_image_path = $image_upload_dir . $unique_image_filename;

                if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $cover_image_path)) {

                    $pdo->beginTransaction();
                    
                    // The path stored in the database should be relative to the web root
                    $db_cover_path = 'saved_image/' . $unique_image_filename;

                    // Insert the new book record
                    $sql_book = "INSERT INTO books (title, author, genre, description, cover_image_path) VALUES (:title, :author, :genre, :description, :cover_image_path)";
                    $stmt_book = $pdo->prepare($sql_book);
                    $stmt_book->bindParam(':title', $title);
                    $stmt_book->bindParam(':author', $author);
                    $stmt_book->bindParam(':genre', $genre);
                    $stmt_book->bindParam(':description', $description);
                    $stmt_book->bindParam(':cover_image_path', $db_cover_path);
                    $stmt_book->execute();
                    $new_book_id = $pdo->lastInsertId();

                    $pdo->commit();
                    $status_message = "New audiobook added successfully!";

                } else {
                    $status_message = "Error uploading cover image. Please check file permissions.";
                }
            } catch (PDOException $e) {
                $pdo->rollBack();
                error_log("Database error: " . $e->getMessage());
                $status_message = "Error adding audiobook: " . $e->getMessage();
            }
        }
    }
    // Redirect back to avoid resubmission on refresh
    header("Location: manage_audiobooks.php?status=" . urlencode($status_message));
    exit;
}

// Now include the header, as all redirect logic is complete
require_once 'includes/admin_header.php';

// Check for status message from URL redirection
if (isset($_GET['status'])) {
    $status_message = htmlspecialchars($_GET['status']);
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
</style>

<div class="bg-gray-900 min-h-screen p-8 text-white">
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
           
            <h1 class="text-4xl font-bold text-blue-500 text-center">Add New Audiobook</h1>
        </div>

        <?php if ($status_message): ?>
            <div id="status-alert" class="bg-blue-600 text-white p-4 rounded-lg mb-4 text-center">
                <?php echo htmlspecialchars($status_message); ?>
            </div>
        <?php endif; ?>

        <div class="bg-gray-800 p-8 rounded-xl shadow-lg">
            <!-- Form for adding a new book -->
            <form action="manage_audiobooks.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_book">
                
                <div class="mb-6">
                    <label for="title" class="block text-gray-300 font-semibold mb-2">Book Title</label>
                    <input type="text" id="title" name="title" required
                           class="w-full px-4 py-2 rounded-lg bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                </div>
                
                <div class="mb-6">
                    <label for="author" class="block text-gray-300 font-semibold mb-2">Author</label>
                    <input type="text" id="author" name="author" required
                           class="w-full px-4 py-2 rounded-lg bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                </div>
                
                <div class="mb-6">
                    <label for="genre" class="block text-gray-300 font-semibold mb-2">Genre</label>
                    <input type="text" id="genre" name="genre" required
                           class="w-full px-4 py-2 rounded-lg bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                </div>
                
                <div class="mb-6">
                    <label for="description" class="block text-gray-300 font-semibold mb-2">Description</label>
                    <textarea id="description" name="description" required
                              class="w-full px-4 py-2 rounded-lg bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors h-32 resize-none"></textarea>
                </div>

                <div class="mb-6">
                    <label for="cover_image" class="block text-gray-300 font-semibold mb-2">Cover Image</label>
                    <input type="file" id="cover_image" name="cover_image" required accept="image/*"
                           class="w-full text-white bg-gray-700 rounded-lg p-2 file:mr-4 file:py-2 file:px-4
                                  file:rounded-full file:border-0 file:text-sm file:font-semibold
                                  file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>
                
                <button type="submit" class="w-full py-3 bg-blue-600 rounded-lg font-bold hover:bg-blue-700 transition-colors">
                    Add New Audiobook
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
});
</script>

<?php
require_once 'includes/admin_footer.php';
?>