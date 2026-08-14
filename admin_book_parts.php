<?php
// FILE: admin_book_parts.php
// This is the admin-facing page to display and manage parts of a specific audiobook.

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database config first
require_once 'includes/db_config.php';

$status_message = '';
$book_id = $_GET['book_id'] ?? null;

// --- Handle AJAX Chunked File Upload (Single File & Folder) ---
// This handles the JavaScript background uploads, chunk by chunk.
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'upload_chunk_ajax') {
    header('Content-Type: application/json');
    $book_id_post = $_POST['book_id'] ?? null;
    $part_number = $_POST['part_number'] ?? null;
    $is_first = $_POST['is_first'] ?? '0';
    
    $chunk_index = isset($_POST['chunk_index']) ? (int)$_POST['chunk_index'] : 0;
    $total_chunks = isset($_POST['total_chunks']) ? (int)$_POST['total_chunks'] : 1;
    $original_filename = $_POST['filename'] ?? 'audio.mp3';

    if (!$book_id_post || !$part_number || !isset($_FILES['audio_file'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid file or book ID.']);
        exit;
    }

    try {
        $new_upload_dir = __DIR__ . '/saved_audio/';
        $book_upload_dir = $new_upload_dir . 'book_' . (int)$book_id_post . '/';

        if (!is_dir($book_upload_dir)) {
            mkdir($book_upload_dir, 0777, true);
        }

        if ($is_first === '1') {
            foreach (glob($book_upload_dir . '*') as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            $stmt = $pdo->prepare("DELETE FROM book_parts WHERE book_id = ?");
            $stmt->execute([(int)$book_id_post]);
        }

        $extension = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
        if (empty($extension)) $extension = 'mp3';
        
        $final_filename = "part_" . (int)$part_number . "." . $extension;
        $final_destination = $book_upload_dir . $final_filename;
        $temp_destination = $final_destination . '.part';

        if ($chunk_index === 0 && file_exists($temp_destination)) {
            unlink($temp_destination);
        }

        $out = fopen($temp_destination, $chunk_index === 0 ? "wb" : "ab");
        $in = fopen($_FILES['audio_file']['tmp_name'], "rb");
        if ($out && $in) {
            while ($buff = fread($in, 4096)) {
                fwrite($out, $buff);
            }
        }
        if ($in) fclose($in);
        if ($out) fclose($out);

        if ($chunk_index === $total_chunks - 1) {
            rename($temp_destination, $final_destination);

            $pdo->beginTransaction();
            $stmt = $pdo->prepare("DELETE FROM book_parts WHERE book_id = ? AND part_number = ?");
            $stmt->execute([(int)$book_id_post, (int)$part_number]);

            $dbPath = "saved_audio/book_" . (int)$book_id_post . "/" . $final_filename;
            $stmt = $pdo->prepare("INSERT INTO book_parts (book_id, part_number, audio_path) VALUES (?, ?, ?)");
            $stmt->execute([(int)$book_id_post, (int)$part_number, $dbPath]);
            $pdo->commit();
        }

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// --- Handle Form Submissions (Delete Part) ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'delete_part') {
    $part_id = $_POST['part_id'] ?? null;
    $book_id_post = $_POST['book_id'] ?? null;

    if ($part_id && $book_id_post) {
        try {
            $pdo->beginTransaction();

            $sql_get_path = "SELECT audio_path FROM book_parts WHERE id = :id";
            $stmt_get_path = $pdo->prepare($sql_get_path);
            $stmt_get_path->bindParam(':id', $part_id, PDO::PARAM_INT);
            $stmt_get_path->execute();
            $path = $stmt_get_path->fetch(PDO::FETCH_ASSOC);

            $new_upload_dir = __DIR__ . '/saved_audio/';
            if ($path && !empty($path['audio_path'])) {
                $audio_file_path = str_replace('saved_audio/', $new_upload_dir, $path['audio_path']);
                if (file_exists($audio_file_path)) {
                    unlink($audio_file_path);
                }
            }

            $sql = "DELETE FROM book_parts WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $part_id, PDO::PARAM_INT);
            $stmt->execute();

            $pdo->commit();
            $status_message = "Audio part deleted successfully!";
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Database error: " . $e->getMessage());
            $status_message = "Error deleting audio part: " . $e->getMessage();
        }
    }
    header("Location: admin_book_parts.php?book_id=" . urlencode($book_id_post) . "&status=" . urlencode($status_message));
    exit;
}

// --- Handle Form Submissions (Add Part) - Fallback ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'add_part') {
    $book_id_post = $_POST['book_id'] ?? null;
    $part_number = $_POST['part_number'] ?? null;

    if (!$book_id_post || !is_numeric($book_id_post) || !$part_number || !is_numeric($part_number) || !isset($_FILES['audio_file'])) {
        $status_message = "Invalid input.";
    } else {
        try {
            $pdo->beginTransaction();

            $new_upload_dir = __DIR__ . '/saved_audio/';
            $book_upload_dir = $new_upload_dir . 'book_' . (int)$book_id_post . '/';

            if (!is_dir($book_upload_dir)) {
                mkdir($book_upload_dir, 0777, true);
            }

            $extension = strtolower(pathinfo($_FILES['audio_file']['name'], PATHINFO_EXTENSION));
            $filename = "part_" . (int)$part_number . "." . $extension;
            $destination = $book_upload_dir . $filename;

            if (file_exists($destination)) {
                unlink($destination);
            }

            $stmt = $pdo->prepare("DELETE FROM book_parts WHERE book_id = ? AND part_number = ?");
            $stmt->execute([(int)$book_id_post, (int)$part_number]);

            if (move_uploaded_file($_FILES['audio_file']['tmp_name'], $destination)) {
                $dbPath = "saved_audio/book_" . (int)$book_id_post . "/" . $filename;

                $stmt = $pdo->prepare("INSERT INTO book_parts (book_id, part_number, audio_path) VALUES (?, ?, ?)");
                $stmt->execute([(int)$book_id_post, (int)$part_number, $dbPath]);

                $pdo->commit();
                $status_message = "Part uploaded successfully.";
            } else {
                $pdo->rollBack();
                $status_message = "File upload failed.";
            }
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $status_message = $e->getMessage();
        }
    }

    header("Location: admin_book_parts.php?book_id=" . urlencode($book_id_post) . "&status=" . urlencode($status_message));
    exit;
}

// Now include header after form redirects complete
require_once 'includes/admin_header.php';

// Check admin role
if (empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: admin_panel.php');
    exit;
}

$book_parts = [];
$book_title = '';
$book_author = '';
$book_genre = '';
$book_description = '';
$book_cover_path = null;

if ($book_id) {
    try {
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

        $sql_parts = "SELECT id, part_number, audio_path FROM book_parts WHERE book_id = :id ORDER BY part_number ASC";
        $stmt_parts = $pdo->prepare($sql_parts);
        $stmt_parts->bindParam(':id', $book_id, PDO::PARAM_INT);
        $stmt_parts->execute();
        $book_parts = $stmt_parts->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $status_message = "Error fetching book details: " . $e->getMessage();
    }
}

if (isset($_GET['status'])) {
    $status_message = htmlspecialchars($_GET['status'], ENT_QUOTES, 'UTF-8');
}
?>

<div class="admin-page-inner admin-page-inner--wide">
    <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4 mb-4 sm:mb-6 min-w-0">
        <div class="flex items-center min-w-0 flex-1">
            
            <a href="javascript:void(0)"
   onclick="history.back();"
   class="text-blue-400 hover:text-blue-300 transition-colors mr-3 sm:mr-4 shrink-0">
    <i class="fas fa-arrow-left"></i>
</a>
            <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-blue-500 break-anywhere min-w-0">
                Manage Parts for: <?php echo htmlspecialchars($book_title, ENT_QUOTES, 'UTF-8'); ?>
            </h1>
        </div>
        <button id="addPartBtn" class="bg-blue-600 text-white py-2 px-4 rounded-lg font-semibold hover:bg-blue-700 transition-colors w-full sm:w-auto shrink-0">
            <i class="fas fa-plus mr-2"></i>Add Part
        </button>
        <button id="uploadFolderBtn" class="bg-green-600 text-white py-2 px-4 rounded-lg font-semibold hover:bg-green-700 transition-colors w-full sm:w-auto shrink-0">
            <i class="fas fa-folder-open mr-2"></i>Upload Folder
        </button>
    </div>
    
    <?php if ($status_message): ?>
        <div id="status-alert" class="bg-blue-600 text-white p-4 rounded-lg mb-4 text-center">
            <?php echo $status_message; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($book_id && $book_title): ?>
    <div class="bg-gray-800 p-4 sm:p-6 rounded-xl shadow-lg mb-4 sm:mb-6 flex flex-col md:flex-row items-center min-w-0">
        <img src="<?php echo htmlspecialchars($book_cover_path ?? 'https://placehold.co/150x225/111827/9CA3AF?text=No+Cover', ENT_QUOTES, 'UTF-8'); ?>" 
             alt="Cover of <?php echo htmlspecialchars($book_title, ENT_QUOTES, 'UTF-8'); ?>" 
             class="w-28 sm:w-36 h-auto object-cover rounded-lg mb-4 md:mb-0 md:mr-6 shadow-md shrink-0">
        <div class="text-center md:text-left min-w-0 w-full">
            <h2 class="text-xl sm:text-2xl font-bold text-white break-anywhere"><?php echo htmlspecialchars($book_title, ENT_QUOTES, 'UTF-8'); ?></h2>
            <p class="text-sm sm:text-md text-gray-400 font-medium break-anywhere">by <?php echo htmlspecialchars($book_author, ENT_QUOTES, 'UTF-8'); ?></p>
            <p class="text-sm text-gray-300 mt-2 break-anywhere">Genre: <span class="font-semibold"><?php echo htmlspecialchars($book_genre, ENT_QUOTES, 'UTF-8'); ?></span></p>
           <p class="text-sm text-gray-300 mt-2 break-anywhere leading-7" style="text-align: justify;">
    <?php echo nl2br(htmlspecialchars($book_description, ENT_QUOTES, 'UTF-8')); ?>
</p>
        </div>
    </div>
    <?php endif; ?>

    <div class="bg-gray-800 p-4 sm:p-6 rounded-xl shadow-lg min-w-0">
        <h2 class="text-xl sm:text-2xl font-semibold text-white mb-4 sm:mb-6">Audio Parts</h2>
        <?php if (empty($book_parts)): ?>
            <p class="text-gray-400 text-center py-6 sm:py-8">No parts found for this book. Use the "Add Part" button to get started.</p>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-6 min-w-0">
                <?php foreach ($book_parts as $part): ?>
                    <div class="bg-gray-700 rounded-xl overflow-hidden shadow-lg p-4 sm:p-6 min-w-0">
                        <?php
                            $filename = basename($part['audio_path']);
                            if (preg_match('/part[_-]?(\d+)/i', $filename, $m)) {
                                $displayPartNumber = (int)$m[1];
                            } elseif (preg_match('/(\d+)/', $filename, $m2)) {
                                $displayPartNumber = (int)$m2[1];
                            } else {
                                $displayPartNumber = (int)$part['part_number'];
                            }
                            $displayTitle = 'Part ' . $displayPartNumber;
                        ?>
                        <h3 class="text-base sm:text-lg font-bold text-white mb-2"><?php echo htmlspecialchars($displayTitle, ENT_QUOTES, 'UTF-8'); ?></h3>
                        <audio controls class="w-full" style="display:block;width:100%;height:54px;margin-top:10px;">
                            <source src="/<?php echo ltrim(htmlspecialchars($part['audio_path'], ENT_QUOTES, 'UTF-8'), '/'); ?>?v=<?php echo time(); ?>" type="audio/mpeg">
                            Your browser does not support the audio element.
                        </audio>
                        <div class="mt-4 text-right sm:text-right">
                            <button class="delete-btn bg-red-600 text-white py-2 px-4 rounded-lg hover:bg-red-700 transition-colors text-sm font-semibold w-full sm:w-auto"
                                    data-part-id="<?php echo (int)$part['id']; ?>"
                                    data-book-id="<?php echo (int)$book_id; ?>"
                                    data-part-title="Part <?php echo (int)$part['part_number']; ?>">
                                <i class="fas fa-trash-alt"></i> Delete Part
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .fade-out {
        opacity: 0;
        transition: opacity 0.5s ease-out;
    }
</style>

<div id="addPartModal" class="sv-modal">
    <div class="sv-modal-content">
        <h2 class="text-xl sm:text-2xl font-semibold text-white mb-4 break-anywhere">Add a New Part to <?php echo htmlspecialchars($book_title, ENT_QUOTES, 'UTF-8'); ?></h2>
        <form action="admin_book_parts.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add_part">
            <input type="hidden" name="book_id" value="<?php echo htmlspecialchars($book_id, ENT_QUOTES, 'UTF-8'); ?>">

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

            <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 sm:gap-4">
                <button type="button" id="cancelAddBtn" class="bg-gray-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-gray-700 transition-colors w-full sm:w-auto">
                    Cancel
                </button>
                <button type="submit" class="bg-blue-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors w-full sm:w-auto">
                    Add Part
                </button>
            </div>
        </form>
    </div>
</div>

<div id="uploadFolderModal" class="sv-modal">
    <div class="sv-modal-content">
        <h2 class="text-xl sm:text-2xl font-semibold text-white mb-4">Upload Folder of Audio Parts</h2>
        <form action="admin_book_parts.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="upload_folder">
            <input type="hidden" name="book_id" value="<?php echo htmlspecialchars($book_id, ENT_QUOTES, 'UTF-8'); ?>">

            <p class="text-gray-400 mb-4">Select a local folder containing MP3 files. Filenames containing numbers will be used to determine part numbers.</p>

            <div class="mb-6">
                <label for="audio_folder" class="block text-gray-300 font-semibold mb-2">Folder of Audio Files</label>
                <input type="file" id="audio_folder" name="audio_folder[]" webkitdirectory directory multiple accept="audio/*"
                       class="w-full text-white bg-gray-700 rounded-lg p-2 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            </div>

            <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 sm:gap-4">
                <button type="button" id="cancelUploadFolderBtn" class="bg-gray-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-gray-700 transition-colors w-full sm:w-auto">
                    Cancel
                </button>
                <button type="submit" class="bg-green-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-green-700 transition-colors w-full sm:w-auto">
                    Upload Folder
                </button>
            </div>
        </form>
    </div>
</div>

<div id="deleteModal" class="sv-modal">
    <div class="sv-modal-content">
        <h2 class="text-xl sm:text-2xl font-semibold text-white mb-4">Confirm Deletion</h2>
        <p class="text-gray-400 mb-6 break-anywhere">Are you sure you want to delete "<span id="deletePartTitle" class="font-bold text-white"></span>"?</p>
        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 sm:gap-4">
            <button type="button" id="cancelDeleteBtn" class="bg-gray-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-gray-700 transition-colors w-full sm:w-auto">
                Cancel
            </button>
            <form id="deleteForm" action="admin_book_parts.php" method="post" class="w-full sm:w-auto">
                <input type="hidden" name="action" value="delete_part">
                <input type="hidden" name="part_id" id="delete-part-id">
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
    const CHUNK_SIZE = 2 * 1024 * 1024; // 2MB chunks

    // --- AJAX Chunked Single File Upload Logic ---
    const addPartForm = document.querySelector('#addPartModal form');
    if (addPartForm) {
        addPartForm.addEventListener('submit', async (e) => {
            e.preventDefault(); 
            
            const fileInput = document.getElementById('audio_file');
            const partNumberInput = document.getElementById('part_number');
            const bookId = addPartForm.querySelector('input[name="book_id"]').value;
            
            if (fileInput.files.length === 0) {
                alert("Please select a file."); return;
            }
            
            const file = fileInput.files[0];
            const partNumber = parseInt(partNumberInput.value);
            
            const submitBtn = addPartForm.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Uploading...';
            
            const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
            let fileUploadSuccess = true;

            for (let chunkIndex = 0; chunkIndex < totalChunks; chunkIndex++) {
                const start = chunkIndex * CHUNK_SIZE;
                const end = Math.min(start + CHUNK_SIZE, file.size);
                const chunk = file.slice(start, end);

                const formData = new FormData();
                formData.append('action', 'upload_chunk_ajax');
                formData.append('book_id', bookId);
                formData.append('part_number', partNumber);
                formData.append('filename', file.name);
                formData.append('chunk_index', chunkIndex);
                formData.append('total_chunks', totalChunks);
                formData.append('audio_file', chunk);
                formData.append('is_first', '0'); 
                
                try {
                    const response = await fetch('admin_book_parts.php', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();
                    if (!result.success) {
                        fileUploadSuccess = false;
                        console.error('Server error:', result.message);
                        break; 
                    }
                } catch (err) {
                    fileUploadSuccess = false;
                    console.error('Network error:', err);
                    break; 
                }
            }
            
            if (fileUploadSuccess) {
                window.location.href = `admin_book_parts.php?book_id=${bookId}&status=${encodeURIComponent('Part uploaded successfully.')}`;
            } else {
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Add Part';
                alert('Upload failed. Check console for details.');
            }
        });
    }

    // --- AJAX Chunked Folder Upload Logic ---
    const uploadFolderForm = document.querySelector('#uploadFolderModal form');
    if (uploadFolderForm) {
        uploadFolderForm.addEventListener('submit', async (e) => {
            e.preventDefault(); 
            
            const fileInput = document.getElementById('audio_folder');
            const bookId = uploadFolderForm.querySelector('input[name="book_id"]').value;
            const files = Array.from(fileInput.files);
            
            if (files.length === 0) {
                alert("Please select a folder."); return;
            }

            files.sort((a, b) => {
                const matchA = a.name.match(/(\d+)/);
                const matchB = b.name.match(/(\d+)/);
                const numA = matchA ? parseInt(matchA[1]) : 9999;
                const numB = matchB ? parseInt(matchB[1]) : 9999;
                return numA - numB;
            });
            
            const submitBtn = uploadFolderForm.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Uploading...';
            
            let uploadedCount = 0;
            let failedCount = 0;

            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                const match = file.name.match(/(\d+)/);
                const partNumber = match ? parseInt(match[1]) : (9999 + i);
                
                const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
                let fileUploadSuccess = true;

                for (let chunkIndex = 0; chunkIndex < totalChunks; chunkIndex++) {
                    const start = chunkIndex * CHUNK_SIZE;
                    const end = Math.min(start + CHUNK_SIZE, file.size);
                    const chunk = file.slice(start, end);
                    
                    const formData = new FormData();
                    formData.append('action', 'upload_chunk_ajax');
                    formData.append('book_id', bookId);
                    formData.append('part_number', partNumber);
                    formData.append('filename', file.name);
                    formData.append('chunk_index', chunkIndex);
                    formData.append('total_chunks', totalChunks);
                    formData.append('audio_file', chunk);
                    formData.append('is_first', (i === 0 && chunkIndex === 0) ? '1' : '0'); 
                    
                    try {
                        const response = await fetch('admin_book_parts.php', {
                            method: 'POST',
                            body: formData
                        });
                        const result = await response.json();
                        if (!result.success) {
                            fileUploadSuccess = false;
                            console.error('Server error on file:', file.name, result.message);
                            break; 
                        }
                    } catch (err) {
                        fileUploadSuccess = false;
                        console.error('Network error on chunk:', err);
                        break; 
                    }
                }
                if (fileUploadSuccess) uploadedCount++; else failedCount++;
            }
            
            window.location.href = `admin_book_parts.php?book_id=${bookId}&status=${encodeURIComponent(`Upload complete: ${uploadedCount} uploaded successfully, ${failedCount} failed.`)}`;
        });
    }

    const statusAlert = document.getElementById('status-alert');
    if (statusAlert) {
        setTimeout(() => {
            statusAlert.classList.add('fade-out');
            setTimeout(() => {
                statusAlert.remove();
            }, 500);
        }, 3000);
    }

    const deleteModal = document.getElementById('deleteModal');
    const deleteButtons = document.querySelectorAll('.delete-btn');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');

    deleteButtons.forEach(button => {
        button.addEventListener('click', () => {
            document.getElementById('deletePartTitle').textContent = button.dataset.partTitle;
            document.getElementById('delete-part-id').value = button.dataset.partId;
            document.getElementById('delete-book-id').value = button.dataset.bookId;
            deleteModal.style.display = 'flex';
        });
    });

    if (cancelDeleteBtn) {
        cancelDeleteBtn.addEventListener('click', () => {
            deleteModal.style.display = 'none';
        });
    }

    const addPartModal = document.getElementById('addPartModal');
    const addPartBtn = document.getElementById('addPartBtn');
    const cancelAddBtn = document.getElementById('cancelAddBtn');

    if (addPartBtn) {
        addPartBtn.addEventListener('click', () => {
            addPartModal.style.display = 'flex';
        });
    }

    if (cancelAddBtn) {
        cancelAddBtn.addEventListener('click', () => {
            addPartModal.style.display = 'none';
        });
    }

    const uploadFolderModal = document.getElementById('uploadFolderModal');
    const uploadFolderBtn = document.getElementById('uploadFolderBtn');
    const cancelUploadFolderBtn = document.getElementById('cancelUploadFolderBtn');

    if (uploadFolderBtn) {
        uploadFolderBtn.addEventListener('click', () => {
            uploadFolderModal.style.display = 'flex';
        });
    }

    if (cancelUploadFolderBtn) {
        cancelUploadFolderBtn.addEventListener('click', () => {
            uploadFolderModal.style.display = 'none';
        });
    }

    window.addEventListener('click', (event) => {
        if (event.target === deleteModal) deleteModal.style.display = 'none';
        if (event.target === addPartModal) addPartModal.style.display = 'none';
        if (event.target === uploadFolderModal) uploadFolderModal.style.display = 'none';
    });

    const allAudioPlayers = document.querySelectorAll('audio');
    allAudioPlayers.forEach(player => {
        player.addEventListener('play', () => {
            allAudioPlayers.forEach(otherPlayer => {
                if (otherPlayer !== player) {
                    otherPlayer.pause();
                }
            });
        });
    });
});
</script>

<?php require_once 'includes/admin_footer.php'; ?>
