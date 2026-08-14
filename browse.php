<?php
// FILE: browse.php
// This is the main page for regular users to browse audiobooks.

session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Redirect to login if the user is not logged in.
if (!isset($_SESSION['user_id'])) {
    header("location: login.php");
    exit;
}

require_once 'includes/header.php';
require_once 'includes/db_config.php';

$audiobooks = [];

// --- Fetch all audiobooks for display ---
try {
    $sql = "SELECT id, title, author, genre, cover_image_path FROM books ORDER BY title ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $audiobooks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Log the error but do not display it to the user for security.
    error_log("Database error: " . $e->getMessage());
}

// Debug helper: show session and cookie state when ?debug=1 is present
if (isset($_GET['debug']) && $_GET['debug'] === '1') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    echo "<pre style='color:#ddd;background:#111;padding:1rem;border-radius:6px;'>";
    echo "Found " . count($audiobooks) . " audiobooks.\n\n";
    foreach ($audiobooks as $b) {
        echo htmlspecialchars(json_encode($b)) . "\n";
    }
    echo "\n-- SESSION INFO --\n";
    echo "session_id: " . session_id() . "\n";
    echo "\$_SESSION:\n" . htmlspecialchars(print_r($_SESSION, true)) . "\n";
    echo "\$_COOKIE:\n" . htmlspecialchars(print_r($_COOKIE, true)) . "\n";
    echo "</pre>";
}


?>

<div class="w-full min-w-0">
        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-blue-500 text-center mb-6 sm:mb-8">Browse Audiobooks</h1>

        <!-- Search Box -->
        <div class="mb-6 sm:mb-8">
            <input type="text" id="searchInput" placeholder="Search by title or author" class="w-full p-3 sm:p-4 rounded-lg bg-gray-800 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <?php if (empty($audiobooks)): ?>
            <p class="text-base sm:text-lg text-center text-gray-400 mt-8 sm:mt-12">No audiobooks are currently available.</p>
        <?php else: ?>
            <div id="audiobook-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6 card-grid-safe">
                <?php foreach ($audiobooks as $book): ?>
                    <div class="audiobook-card bg-gray-800 p-4 rounded-xl shadow-2xl flex flex-col items-center text-center transition-transform duration-300 hover-scale-card relative min-w-0 w-full" data-title="<?php echo htmlspecialchars($book['title']); ?>" data-author="<?php echo htmlspecialchars($book['author']); ?>" data-genre="<?php echo htmlspecialchars($book['genre']); ?>">
                        <img src="<?php echo htmlspecialchars($book['cover_image_path']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?> Cover" class="w-full max-w-[12rem] aspect-square rounded-lg object-cover mb-4 shadow-lg" onerror="this.onerror=null;this.src='https://placehold.co/200x200/1f2937/d1d5db?text=No+Cover';">
                        <h3 class="text-base sm:text-lg font-bold text-white mt-2 break-anywhere w-full"><?php echo htmlspecialchars($book['title']); ?></h3>
                        <p class="text-gray-400 text-sm break-anywhere w-full">by <?php echo htmlspecialchars($book['author']); ?></p>

                        <div class="mt-4 w-full">
                            <a href="play.php?id=<?php echo htmlspecialchars($book['id']); ?>" class="toggle-btn bg-green-600 hover:bg-green-700 transition-colors duration-300 text-white font-semibold py-2 px-4 rounded-full w-full inline-block text-center">
                                Listen
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
</div>

<script>
    const searchInput = document.getElementById('searchInput');
    const audiobookCards = document.querySelectorAll('.audiobook-card');

    // Search functionality
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
</script>

<?php
require_once 'includes/footer.php';
?>