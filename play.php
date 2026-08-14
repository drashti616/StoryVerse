<?php
// FILE: play.php
// This page displays a single audiobook's details and a player for each part.

// Start session and add no-cache headers, then enforce auth before output
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?auth_required=1");
    exit;
}

require_once 'includes/header.php';
require_once 'includes/db_config.php';

// Check for the audiobook ID in the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: browse.php");
    exit;
}

$book_id = $_GET['id'];
$book = null;
$parts = [];

// --- Fetch a single audiobook from the database ---
try {
    $sql_book = "SELECT id, title, author, genre, description, cover_image_path
             FROM books WHERE id = :id";
    $stmt_book = $pdo->prepare($sql_book);
    $stmt_book->bindParam(':id', $book_id, PDO::PARAM_INT);
    $stmt_book->execute();
    $book = $stmt_book->fetch(PDO::FETCH_ASSOC);

    if ($book) {
        $sql_parts = "SELECT id, part_number, audio_path FROM book_parts WHERE book_id = :book_id ORDER BY part_number ASC";
        $stmt_parts = $pdo->prepare($sql_parts);
        $stmt_parts->bindParam(':book_id', $book_id, PDO::PARAM_INT);
        $stmt_parts->execute();
        $parts = $stmt_parts->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
}
?>

<?php if (!$book): ?>
	    <div class="w-full min-w-0 max-w-6xl mx-auto px-4 sm:px-6">
<h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-blue-500 mb-6 sm:mb-8">Audiobook Not Found</h1>
            <p class="text-base sm:text-lg text-gray-400">The audiobook you are looking for does not exist.</p>
    </div>
<?php else: ?>
    <div class="w-full min-w-0 max-w-4xl mx-auto">
        
        <button
    type="button"
    onclick="history.back();"
    class="text-blue-400 hover:text-blue-300 transition-colors mb-4 sm:mb-6 inline-block font-medium">
    <i class="fas fa-arrow-left mr-2"></i>Back to Browse
</button>
            
        <!-- Book Information Card (Styled exactly like admin_book_parts.php) -->
        <div class="bg-gray-800 p-4 sm:p-6 rounded-xl shadow-lg mb-4 sm:mb-6 flex flex-col md:flex-row items-center min-w-0">
            <img src="<?php echo htmlspecialchars($book['cover_image_path']); ?>" 
                 alt="Cover of <?php echo htmlspecialchars($book['title']); ?>" 
                 class="w-28 sm:w-36 h-auto object-cover rounded-lg mb-4 md:mb-0 md:mr-6 shadow-md shrink-0"
                 onerror="this.onerror=null;this.src='https://placehold.co/150x225/111827/9CA3AF?text=No+Cover';">
                 
            <div class="text-center md:text-left min-w-0 w-full">
                <h2 class="text-xl sm:text-2xl font-bold text-white break-anywhere"><?php echo htmlspecialchars($book['title']); ?></h2>
                <p class="text-sm sm:text-md text-gray-400 font-medium break-anywhere">by <?php echo htmlspecialchars($book['author']); ?></p>
                <p class="text-sm text-gray-300 mt-2 break-anywhere">Genre: <span class="font-semibold"><?php echo htmlspecialchars($book['genre']); ?></span></p>
                <p class="text-sm text-gray-300 mt-2 break-anywhere leading-7" style="text-align: justify;">
                    <?php echo nl2br(htmlspecialchars($book['description'])); ?>
                </p>
            </div>
        </div>

        <!-- Audio Parts Card (Now using a side-by-side grid layout) -->
        <div class="bg-gray-800 p-4 sm:p-6 rounded-xl shadow-lg min-w-0">
            <h2 class="text-xl sm:text-2xl font-semibold text-white mb-4 sm:mb-6">Book Parts</h2>
            
            <?php if (empty($parts)): ?>
                <p class="text-gray-400 text-center py-6 sm:py-8">No parts found for this audiobook.</p>
            <?php else: ?>
                      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 min-w-0">
                    <?php foreach ($parts as $part): ?>
                        <div class="bg-gray-700 rounded-xl overflow-hidden shadow-lg p-4 sm:p-6 min-w-0">
                            <h3 class="text-base sm:text-lg font-bold text-white mb-2">Part <?php echo htmlspecialchars($part['part_number']); ?></h3>
                            <audio controls class="w-full" style="display:block;width:100%;height:54px;margin-top:10px;">
                                <source src="/<?php echo ltrim(htmlspecialchars($part['audio_path']), '/'); ?>?v=<?php echo time(); ?>" type="audio/mpeg">
                                Your browser does not support the audio element.
                            </audio>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
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

<?php endif; ?>

<?php
require_once 'includes/footer.php';
?>
