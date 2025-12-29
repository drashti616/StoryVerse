<?php
// FILE: includes/header.php
// This reusable header contains the navigation bar and starts the user session.


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$is_logged_in = isset($_SESSION['user_id']);
$user_role = $_SESSION['user_role'] ?? ''; // Get the user's role from session

// Prevent browsers from caching authenticated pages so back button doesn't show stale content
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audiobook Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> 
        body { 
            font-family: 'Inter', sans-serif;
        } 
        /* Custom modal overlay style */
        .modal-overlay {
            background-color: rgba(0, 0, 0, 0.75);
        }
        /* Custom scrollbar for mobile sidebar */
        #mobile-sidebar::-webkit-scrollbar {
            width: 6px;
        }
        #mobile-sidebar::-webkit-scrollbar-track {
            background: #1f2937;
        }
        #mobile-sidebar::-webkit-scrollbar-thumb {
            background: #4b5563;
            border-radius: 3px;
        }
        #mobile-sidebar::-webkit-scrollbar-thumb:hover {
            background: #6b7280;
        }
    </style>
    <script>
        // Ensure that if this page is restored from the back/forward cache after logout,
        // it immediately refreshes to re-check authentication (no manual F5 required).
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>
</head>
<body class="bg-gray-900 text-gray-300">

    <div id="logout-modal" class="fixed inset-0 hidden items-center justify-center modal-overlay z-[9999]">
        <div class="bg-gray-800 p-6 rounded-lg shadow-2xl max-w-sm w-full transform transition-all duration-300 scale-95 opacity-0">
            <h3 class="text-xl font-bold text-red-500 mb-4 flex items-center">
                <i class="fas fa-exclamation-triangle mr-3"></i> Confirm Logout
            </h3>
            <p class="text-gray-300 mb-6">Are you sure you want to log out of your account?</p>
            <div class="flex justify-end space-x-3">
                <button id="cancel-logout" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-500 transition-colors">
                    Cancel
                </button>
                <a href="logout.php" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors" id="confirm-logout-btn">
                    Yes, Log Me Out
                </a>
            </div>
        </div>
    </div>
    <!-- Desktop Header -->
    <header class="bg-gray-800 shadow-md sticky top-0 z-50 hidden lg:block">
        <nav class="container mx-auto p-4 flex items-center justify-between">
           
            <h2 class="text-2xl font-bold text-blue-500 hover:text-blue-400 transition-colors">StoryVerse</h2>
            <ul class="flex space-x-8 items-center text-lg">
                <li>
                    <a href="ai.php" class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white rounded-full 
                        bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 transition-all duration-300" title="AI Studio">
                        <i class="fas fa-magic mr-2"></i> AI Studio
                        <span class="ml-2 inline-flex items-center rounded-full bg-pink-600 px-2 py-0.5 text-xs font-bold text-white">New</span>
                    </a>
                </li>
                <li><a href="browse.php" class="hover:text-blue-500 transition-colors">Browse</a></li>
                <li><a href="user_messages.php" class="hover:text-blue-500 transition-colors">Messages</a></li>
                <li><a href="contact.php" class="hover:text-blue-500 transition-colors">Contact Us</a></li>
                <li><a href="about.php" class="hover:text-blue-500 transition-colors">About Us</a></li>
                <li><a href="profile.php" class="hover:text-blue-500 transition-colors">Profile</a></li>
                <li><button id="logout-trigger-user-desktop" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors">Logout</button></li>
            </ul>
        </nav>
    </header>

    <!-- Mobile Header -->
    <header class="bg-gray-800 shadow-md p-4 sticky top-0 z-40 lg:hidden">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <button id="mobile-menu-btn" class="text-white focus:outline-none">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <h2 class="text-blue-500 hover:text-blue-400 text-2xl font-bold">StoryVerse</h2>
            </div>
        </div>
    </header>

    <!-- Mobile Sidebar (Fixed Width - Only on Mobile) -->
    <aside id="mobile-sidebar" class="bg-gray-800 text-white w-64 fixed top-0 left-0 h-full p-4 transform -translate-x-full transition-transform duration-300 ease-in-out z-50 lg:hidden overflow-y-auto">
        <div class="flex flex-col min-h-full">
            <h2 class="text-3xl font-bold text-blue-500 hover:text-blue-400 transition-colors text-left mb-4 flex-shrink-0">StoryVerse</h2>
            <nav class="flex-grow py-2">
                <ul class="space-y-4">
                    <li>
                        <a href="ai.php" class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white rounded-full 
                        bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 transition-all duration-300">
                            <i class="fas fa-magic w-6 mr-3"></i>
                            AI Studio
                            <span class="ml-auto inline-flex items-center rounded-full bg-pink-600 px-2 py-0.5 text-xs font-bold text-white">New</span>
                        </a>
                    </li>
                    <li>
                        <a href="browse.php" class="flex items-center p-3 rounded-lg hover:bg-gray-700 transition-colors">
                            <i class="fas fa-book w-6 mr-3"></i>
                            Browse
                        </a>
                    </li>
                    <li>
                        <a href="user_messages.php" class="flex items-center p-3 rounded-lg hover:bg-gray-700 transition-colors">
                            <i class="fas fa-envelope w-6 mr-3"></i>
                            Messages
                        </a>
                    </li>
                    <li>
                        <a href="contact.php" class="flex items-center p-3 rounded-lg hover:bg-gray-700 transition-colors">
                            <i class="fas fa-phone w-6 mr-3"></i>
                            Contact Us
                        </a>
                    </li>
                    <li>
                        <a href="about.php" class="flex items-center p-3 rounded-lg hover:bg-gray-700 transition-colors">
                            <i class="fas fa-info-circle w-6 mr-3"></i>
                            About Us
                        </a>
                    </li>
                    <li>
                        <a href="profile.php" class="flex items-center p-3 rounded-lg hover:bg-gray-700 transition-colors">
                            <i class="fas fa-user w-6 mr-3"></i>
                            Profile
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="mt-auto pt-4 border-t border-gray-700 flex-shrink-0">
                <button id="logout-trigger-user-mobile" class="w-full flex items-center p-3 rounded-lg hover:bg-red-500 transition-colors mt-2 text-left">
                    <i class="fas fa-sign-out-alt w-6 mr-3"></i>
                    Logout
                </button>
            </div>
        </div>
    </aside>

    <div id="sidebar-overlay" class="fixed inset-0 bg-black opacity-50 z-40 hidden"></div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const mobileMenuBtn = document.getElementById('mobile-menu-btn');
            const mobileSidebar = document.getElementById('mobile-sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            // --- LOGOUT MODAL ELEMENTS ---
            const logoutTriggerUserDesktop = document.getElementById('logout-trigger-user-desktop');
            const logoutTriggerUserMobile = document.getElementById('logout-trigger-user-mobile');
            const logoutModal = document.getElementById('logout-modal');
            const cancelLogoutBtn = document.getElementById('cancel-logout');
            const modalContent = logoutModal.querySelector('div'); // The inner content for transition effect

            // --- Modal Functions ---
            function openModal() {
                logoutModal.classList.remove('hidden');
                logoutModal.classList.add('flex');
                setTimeout(() => {
                    modalContent.classList.remove('opacity-0', 'scale-95');
                    modalContent.classList.add('opacity-100', 'scale-100');
                }, 10);
            }

            function closeModal() {
                modalContent.classList.remove('opacity-100', 'scale-100');
                modalContent.classList.add('opacity-0', 'scale-95');
                setTimeout(() => {
                    logoutModal.classList.remove('flex');
                    logoutModal.classList.add('hidden');
                }, 300); // Wait for transition to finish
            }

            // --- Mobile Sidebar Functions ---
            function openMobileSidebar() {
                mobileSidebar.classList.remove('-translate-x-full');
                mobileSidebar.classList.add('translate-x-0');
                overlay.classList.remove('hidden');
                // Lock body scroll when sidebar is open
                document.body.style.overflow = 'hidden';
            }

            function closeMobileSidebar() {
                mobileSidebar.classList.remove('translate-x-0');
                mobileSidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
                // Unlock body scroll when sidebar is closed
                document.body.style.overflow = '';
            }

            if (mobileMenuBtn) {
                mobileMenuBtn.addEventListener('click', openMobileSidebar);
            }
            if (overlay) {
                overlay.addEventListener('click', closeMobileSidebar);
            }

            // Close sidebar when clicking on sidebar links
            const sidebarLinks = mobileSidebar.querySelectorAll('a');
            sidebarLinks.forEach(link => {
                link.addEventListener('click', () => {
                    closeMobileSidebar();
                });
            });

            // Close sidebar on window resize to desktop
            window.addEventListener('resize', () => {
                if (window.innerWidth >= 1024) {
                    // On desktop, close mobile sidebar and unlock scroll
                    // Check if sidebar is open (translate-x-0 means it's visible)
                    if (!mobileSidebar.classList.contains('-translate-x-full') && 
                        mobileSidebar.classList.contains('translate-x-0')) {
                        closeMobileSidebar();
                    }
                    document.body.style.overflow = '';
                }
            });

            if (logoutTriggerUserDesktop) {
                logoutTriggerUserDesktop.addEventListener('click', (e) => {
                    e.preventDefault();
                    openModal();
                });
            }

            if (logoutTriggerUserMobile) {
                logoutTriggerUserMobile.addEventListener('click', (e) => {
                    e.preventDefault();
                    closeMobileSidebar();
                    openModal();
                });
            }

            if (cancelLogoutBtn) {
                cancelLogoutBtn.addEventListener('click', closeModal);
            }
            
            // Close modal if overlay is clicked
            if (logoutModal) {
                logoutModal.addEventListener('click', (e) => {
                    if (e.target === logoutModal) {
                        closeModal();
                    }
                });
            }
        });
    </script>
    <main class="container mx-auto p-8 min-h-screen">