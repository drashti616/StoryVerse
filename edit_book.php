<?php
// FILE: edit_book.php
// This is the administrative page for editing an existing audiobook.

// Use admin header which enforces admin session and adds no-cache headers before any output
require_once 'includes/admin_header.php';
require_once 'includes/db_config.php';

$message = '';
$book = null;
$book_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch book data
if ($book_id > 0) {
    $sql = "SELECT * FROM audiobooks WHERE id = :id";
    if ($stmt = $pdo->prepare($sql)) {
        $stmt->bindParam(':id', $book_id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            $book = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
}

if (!$book) {
    // Redirect if book not found
    header("location: admin_panel.php");
    exit;
}

// Handle form submission for updates
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_book'])) {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $description = trim($_POST['description']);

    // Start with a base update SQL
    $sql_update = "UPDATE audiobooks SET title = :title, author = :author, description = :description";
    $params = [
        ':title' => $title,
        ':author' => $author,
        ':description' => $description,
        ':id' => $book_id
    ];
    $update_files = false;

    // Check if new files are uploaded
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['size'] > 0) {
        $image_dir = 'assets/uploads/images/';
        $new_image_path = $image_dir . uniqid() . '_' . basename($_FILES['cover_image']['name']);
        if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $new_image_path)) {
            // Delete old image file
            if (file_exists($book['cover_image_path'])) {
                unlink($book['cover_image_path']);
            }
            $sql_update .= ", cover_image_path = :cover_image_path";
            $params[':cover_image_path'] = $new_image_path;
            $update_files = true;
        } else {
            $message = "<div class='bg-red-500 text-white p-3 rounded-lg mb-4'>Error uploading new cover image.</div>";
        }
    }

    if (isset($_FILES['audio_file']) && $_FILES['audio_file']['size'] > 0) {
        $audio_dir = 'assets/uploads/audio/';
        $new_audio_path = $audio_dir . uniqid() . '_' . basename($_FILES['audio_file']['name']);
        if (move_uploaded_file($_FILES['audio_file']['tmp_name'], $new_audio_path)) {
            // Delete old audio file
            if (file_exists($book['audio_file_path'])) {
                unlink($book['audio_file_path']);
            }
            $sql_update .= ", audio_file_path = :audio_file_path";
            $params[':audio_file_path'] = $new_audio_path;
            $update_files = true;
        } else {
            $message = "<div class='bg-red-500 text-white p-3 rounded-lg mb-4'>Error uploading new audio file.</div>";
        }
    }

    $sql_update .= " WHERE id = :id";
    
    if ($stmt = $pdo->prepare($sql_update)) {
        if ($stmt->execute($params)) {
            $message = "<div class='bg-green-500 text-white p-3 rounded-lg mb-4'>Audiobook updated successfully!</div>";
            // Re-fetch book data to display updated info on the same page
            $sql = "SELECT * FROM audiobooks WHERE id = :id";
            if ($re_fetch_stmt = $pdo->prepare($sql)) {
                $re_fetch_stmt->bindParam(':id', $book_id, PDO::PARAM_INT);
                $re_fetch_stmt->execute();
                $book = $re_fetch_stmt->fetch(PDO::FETCH_ASSOC);
            }
        } else {
            $message = "<div class='bg-red-500 text-white p-3 rounded-lg mb-4'>Error updating audiobook in database.</div>";
        }
    }
}
?>

<h1 class="text-4xl font-bold text-blue-500 text-center mb-6">Edit Audiobook</h1>
<div class="bg-gray-800 p-8 rounded-lg shadow-xl max-w-2xl mx-auto">
    <a href="admin_panel.php" class="inline-block mb-4 text-blue-400 hover:underline"><i class="fa-solid fa-arrow-left mr-2"></i>Back to Admin Panel</a>
    <?php echo $message; ?>
    <form action="edit_book.php?id=<?php echo $book_id; ?>" method="post" enctype="multipart/form-data" class="space-y-4">
        <div>
            <label for="title" class="block text-gray-400 mb-2">Title</label>
            <input type="text" name="title" id="title" class="w-full bg-gray-700 text-white p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?php echo htmlspecialchars($book['title']); ?>" required>
        </div>
        <div>
            <label for="author" class="block text-gray-400 mb-2">Author</label>
            <input type="text" name="author" id="author" class="w-full bg-gray-700 text-white p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?php echo htmlspecialchars($book['author']); ?>" required>
        </div>
        <div>
            <label for="description" class="block text-gray-400 mb-2">Description</label>
            <textarea name="description" id="description" rows="4" class="w-full bg-gray-700 text-white p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($book['description']); ?></textarea>
        </div>
        <div class="flex items-center space-x-4">
            <div class="flex-shrink-0">
                 <p class="text-gray-400 mb-2">Current Cover</p>
                <img src="<?php echo htmlspecialchars($book['cover_image_path']); ?>" alt="Current Cover" class="w-24 h-24 object-cover rounded-md">
            </div>
            <div class="flex-grow">
                <label for="cover_image" class="block text-gray-400 mb-2">Replace Cover Image (optional)</label>
                <input type="file" name="cover_image" id="cover_image" class="w-full bg-gray-700 text-white p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
        <div class="flex items-center space-x-4">
             <div class="flex-shrink-0">
                 <p class="text-gray-400 mb-2">Current Audio</p>
                 <audio controls class="w-full">
                     <source src="<?php echo htmlspecialchars($book['audio_file_path']); ?>" type="audio/mpeg">
                     Your browser does not support the audio element.
                 </audio>
             </div>
             <div class="flex-grow">
                <label for="audio_file" class="block text-gray-400 mb-2">Replace Audio File (optional)</label>
                <input type="file" name="audio_file" id="audio_file" class="w-full bg-gray-700 text-white p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
        <button type="submit" name="update_book" class="w-full bg-blue-600 text-white p-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors">Update Audiobook</button>
    </form>
</div>

<?php require_once 'includes/admin_footer.php'; ?>

