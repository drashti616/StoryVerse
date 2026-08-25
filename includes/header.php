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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="includes/responsive.css">
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
       window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
        window.location.reload();
    }
});
    </script>
</head>
<body class="bg-gray-900 text-gray-300 overflow-x-hidden">

    <div id="logout-modal" class="fixed inset-0 hidden items-center justify-center modal-overlay z-[9999] px-4">
        <div class="bg-gray-800 p-6 rounded-lg shadow-2xl max-w-sm w-full modal-safe transform transition-all duration-300 scale-95 opacity-0">
            <h3 class="text-xl font-bold text-red-500 mb-4 flex items-center">
                <i class="fas fa-exclamation-triangle mr-3"></i> Confirm Logout
            </h3>
            <p class="text-gray-300 mb-6">Are you sure you want to log out of your account?</p>
            <div class="flex flex-col-reverse sm:flex-row justify-end gap-3">
                <button id="cancel-logout" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-500 transition-colors w-full sm:w-auto">
                    Cancel
                </button>
                <a href="logout.php" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors text-center w-full sm:w-auto" id="confirm-logout-btn">
                    Yes, Log Me Out
                </a>
            </div>
        </div>
    </div>
    <!-- Desktop Header -->
    <header class="bg-gray-800 shadow-md fixed top-0 left-0 right-0 z-50 hidden lg:block">
        <nav class="container mx-auto px-4 py-3 xl:py-4 flex flex-wrap items-center justify-between gap-3 min-w-0">
           
            <h2 class="text-xl xl:text-2xl font-bold text-blue-500 hover:text-blue-400 transition-colors shrink-0">StoryVerse</h2>
            <ul class="flex flex-wrap justify-end gap-x-3 xl:gap-x-6 gap-y-2 items-center text-sm xl:text-lg min-w-0">
               
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
	<header class="bg-gray-800 shadow-md p-4 fixed top-0 left-0 right-0 z-40 lg:hidden">
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
		<aside id="mobile-sidebar"
       class="bg-gray-800 text-white w-64 fixed top-0 left-0 h-screen p-4 transform -translate-x-full transition-transform duration-300 ease-in-out z-50 lg:hidden overflow-hidden">
        <div class="flex flex-col min-h-full">
            <h2 class="text-3xl font-bold text-blue-500 hover:text-blue-400 transition-colors text-left mb-4 flex-shrink-0">StoryVerse</h2>
            <nav class="flex-grow py-2">
                <ul class="space-y-4">
                   
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
                    <li>
    					<button id="logout-trigger-user-mobile"
            			class="w-full flex items-center p-3 rounded-lg bg-red-600 hover:bg-red-700 text-white transition-colors text-left">
        				<i class="fas fa-sign-out-alt w-6 mr-3 shrink-0"></i>
        				<span class="truncate font-semibold">Logout</span>
    					</button>
					</li>
                </ul>
           
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

                document.body.style.overflow = 'hidden';
                document.body.style.touchAction = 'none';
            }

           function closeMobileSidebar() {
                mobileSidebar.classList.remove('translate-x-0');
                mobileSidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');

                document.body.style.overflow = '';
                document.body.style.touchAction = '';
            }

            // --- Swipe left to close mobile sidebar ---
                let touchStartX = 0;
                let touchEndX = 0;

                mobileSidebar.addEventListener('touchstart', (e) => {
                    touchStartX = e.changedTouches[0].screenX;
                }, { passive: true });

                mobileSidebar.addEventListener('touchend', (e) => {
                    touchEndX = e.changedTouches[0].screenX;

                    const swipeDistance = touchEndX - touchStartX;

                    // Swipe from right to left
                    if (swipeDistance < -50) {
                        closeMobileSidebar();
                    }
                }, { passive: true });

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
	<main class="container mx-auto px-4 pt-24 pb-6 sm:px-6 sm:pt-24 sm:pb-8 lg:px-8 min-h-screen min-w-0 w-full max-w-7xl">
