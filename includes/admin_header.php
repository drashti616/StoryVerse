<?php
// FILE: includes/admin_header.php
// This file contains the header and a responsive navigation bar for the admin panel.
// It handles session management and ensures only authenticated admins can access the page.


session_start();

// Prevent caching to avoid back-button showing stale admin pages after logout
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// Check if the user is logged in and is an admin, otherwise redirect to the login page.
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("location: login.php");
    exit;
}

$username = $_SESSION['username'] ?? 'Admin';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="includes/responsive.css">
    <style>
        html.sidebar-open,
		body.sidebar-open {
    		overflow: hidden;
    		height: 100%;
    		touch-action: none;
		}
        body { 
            font-family: 'Inter', sans-serif;
        }
        /* Custom modal overlay style */
        .modal-overlay {
            background-color: rgba(0, 0, 0, 0.75);
        }
        
    </style>
    <script>
        // Force revalidation when page is restored from BFCache (back/forward navigation)
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
            <p class="text-gray-300 mb-6">Are you sure you want to end your administrative session?</p>
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
    <!-- Mobile Sticky Header -->
    <header class="bg-gray-800 shadow-md p-4 fixed top-0 left-0 right-0 z-40 lg:hidden">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <button id="mobile-menu-btn" class="text-white focus:outline-none">
                    <i class="fas fa-bars text-xl"></i>
                </button>

                <h2 class="text-blue-500 hover:text-blue-400 text-2xl font-bold">
                    StoryVerse
                </h2>
            </div>
        </div>
    </header>

    <div class="admin-shell flex w-full max-w-full min-h-screen overflow-x-hidden">
    <aside id="sidebar" class="admin-sidebar bg-gray-800 text-white w-64 fixed top-0 left-0 h-screen p-4 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out z-50 overflow-hidden">
        <div class="flex flex-col min-h-full overflow-hidden">
            <h2 class="text-3xl font-bold text-blue-500 hover:text-blue-400 transition-colors text-left">StoryVerse</h2>
            <nav class="flex-grow">
                <ul class="space-y-4">
                    <li>
                        <a href="admin_panel.php" class="flex items-center p-3 rounded-lg hover:bg-gray-700 transition-colors min-w-0">
                            <i class="fas fa-inbox w-6 mr-3 shrink-0"></i>
                            <span class="truncate">Messages</span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="manage_users.php" class="flex items-center p-3 rounded-lg hover:bg-gray-700 transition-colors min-w-0">
                            <i class="fas fa-users w-6 mr-3 shrink-0"></i>
                            <span class="truncate">Users</span>
                        </a>
                    </li>
                    <li>
                        <a href="manage_audiobooks.php" class="flex items-center p-3 rounded-lg hover:bg-gray-700 transition-colors min-w-0">
                            <i class="fas fa-book-open w-6 mr-3 shrink-0"></i>
                            <span class="truncate">Add Audiobooks</span>
                        </a>
                    </li>

                     <li>
  						 <a href="existing_audiobooks.php" class="flex items-center p-3 rounded-lg hover:bg-gray-700 transition-colors min-w-0">
     					 <i class="fas fa-book w-6 mr-3 shrink-0"></i>
        				<span class="truncate">Existing Audiobooks</span>
   						</a>
					</li>
						<li>
                        <a href="admin_profile.php" class="flex items-center p-3 rounded-lg hover:bg-gray-700 transition-colors min-w-0">
                            <i class="fas fa-user w-6 mr-3 shrink-0"></i>
                            <span class="truncate">Profile</span>
                        </a>
                    </li>
					<li>
    					<button id="logout-trigger-admin"
            			class="w-full flex items-center p-3 rounded-lg bg-red-600 hover:bg-red-700 text-white transition-colors text-left">
        				<i class="fas fa-sign-out-alt w-6 mr-3 shrink-0"></i>
        				<span class="truncate font-semibold">Logout</span>
    					</button>
					</li>

                </ul>
     
        </div>
    </aside>

    <div id="sidebar-overlay" class="fixed inset-0 bg-black opacity-50 z-40 hidden lg:hidden"></div>

<div class="admin-content-area flex flex-col flex-1 min-w-0 w-full lg:ml-64">
	<main class="flex-1 px-4 pt-24 pb-6 sm:px-6 sm:pt-24 sm:pb-8 lg:px-8 lg:pt-6 min-w-0 w-full admin-main-content">
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const mobileMenuBtn = document.getElementById('mobile-menu-btn');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            // --- LOGOUT MODAL ELEMENTS ---
            const logoutTriggerAdmin = document.getElementById('logout-trigger-admin');
            const logoutModal = document.getElementById('logout-modal');
            const cancelLogoutBtn = document.getElementById('cancel-logout');
            const modalContent = logoutModal.querySelector('div'); // The inner content for transition effect
            
            const isLgScreen = () => window.innerWidth >= 1024;

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

            // --- Event Listeners ---
            if (logoutTriggerAdmin) {
                logoutTriggerAdmin.addEventListener('click', openModal);
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


            // --- Sidebar Functions ---
            function openSidebar() {
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0');
                overlay.classList.remove('hidden');

                // Lock the entire page underneath the sidebar
                document.documentElement.classList.add('sidebar-open');
                document.body.classList.add('sidebar-open');
            }

           function closeSidebar() {
                sidebar.classList.remove('translate-x-0');
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');

                // Restore normal page scrolling
                document.documentElement.classList.remove('sidebar-open');
                document.body.classList.remove('sidebar-open');
            }
            
            // --- Swipe left to close sidebar on mobile ---
            let touchStartX = 0;

            sidebar.addEventListener('touchstart', (e) => {
                if (!isLgScreen()) {
                    touchStartX = e.changedTouches[0].screenX;
                }
            }, { passive: true });

            sidebar.addEventListener('touchend', (e) => {
                if (!isLgScreen()) {
                    const touchEndX = e.changedTouches[0].screenX;
                    const swipeDistance = touchEndX - touchStartX;

                    // Close when swiped from right to left by at least 50px
                    if (swipeDistance < -50) {
                        closeSidebar();
                    }
                }
            }, { passive: true });

            if (mobileMenuBtn) {
                mobileMenuBtn.addEventListener('click', openSidebar);
            }
            if (overlay) {
                overlay.addEventListener('click', closeSidebar);
            }

            // Close sidebar when clicking on sidebar links (optional, for better UX)
            const sidebarLinks = sidebar.querySelectorAll('a');
            sidebarLinks.forEach(link => {
                link.addEventListener('click', () => {
                    if (!isLgScreen()) {
                        closeSidebar();
                    }
                });
            });

            // Add a resize event listener to handle changes in screen size
            window.addEventListener('resize', () => {
            if (isLgScreen()) {
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0');
                overlay.classList.add('hidden');

                // Ensure page scrolling is restored on desktop
                document.documentElement.classList.remove('sidebar-open');
                document.body.classList.remove('sidebar-open');
            } else {
                // Reset sidebar and scrolling when switching to mobile
                closeSidebar();
            }
        });

            // Highlight the active sidebar link
            const currentPage = window.location.pathname.split('/').pop() || 'admin_panel.php';
            sidebar.querySelectorAll('nav a').forEach(link => {
                const href = link.getAttribute('href');
                if (href === currentPage) {
                    link.classList.add('bg-gray-700', 'text-blue-400');
                }
            });
        });
    </script>
