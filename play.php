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
    $sql_book = "SELECT id, title, author, description, cover_image_path FROM books WHERE id = :id";
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

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    body { font-family: 'Inter', sans-serif; }
</style>

<?php if (!$book): ?>
    <div class="bg-gray-900 min-h-screen p-8 text-white font-sans">
        <div class="max-w-7xl mx-auto">
            <h1 class="text-4xl font-bold text-blue-500 text-center mb-8">Audiobook Not Found</h1>
            <p class="text-lg text-center text-gray-400 mt-12">The audiobook you are looking for does not exist.</p>
        </div>
    </div>
<?php else: ?>
    <div class="bg-gray-900 min-h-screen p-8 text-white font-sans">
        <div class="max-w-4xl mx-auto">
            <a href="browse.php" class="text-blue-400 hover:underline mb-8 inline-block">&larr; Back to Browse</a>
            
            <div class="bg-gray-800 p-8 rounded-xl shadow-2xl flex flex-col lg:flex-row items-center lg:items-start text-center lg:text-left gap-8">
                <img src="<?php echo htmlspecialchars($book['cover_image_path']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?> Cover" class="w-full max-w-sm rounded-lg object-cover shadow-lg" onerror="this.onerror=null;this.src='https://placehold.co/400x400/1f2937/d1d5db?text=No+Cover';">
                
                <div class="flex flex-col items-center lg:items-start w-full">
                    <h1 class="text-4xl font-bold text-white mt-4 lg:mt-0 mb-2"><?php echo htmlspecialchars($book['title']); ?></h1>
                    <p class="text-gray-400 text-lg mb-4">by <?php echo htmlspecialchars($book['author']); ?></p>
                    
                    <div class="description mt-4 text-gray-300 w-full mb-8">
                        <p><?php echo htmlspecialchars($book['description']); ?></p>
                    </div>

                    <h2 class="text-2xl font-semibold text-white mt-8 mb-4">Book Parts</h2>
                    <?php if (empty($parts)): ?>
                        <p class="text-gray-400">No parts found for this audiobook.</p>
                    <?php else: ?>
                        <div class="w-full space-y-4">
                            <?php foreach ($parts as $part): ?>
                                <div class="bg-gray-700 p-4 rounded-lg">
                                    <h3 class="text-white font-bold mb-2">Part <?php echo htmlspecialchars($part['part_number']); ?></h3>
                                    <audio controls class="w-full">
                                        <source src="<?php echo htmlspecialchars($part['audio_path']); ?>" type="audio/mpeg">
                                        Your browser does not support the audio element.
                                    </audio>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
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