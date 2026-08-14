<?php
// FILE: about.php
// The About Us page with more comprehensive content.

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
require_once 'includes/db_config.php';?>

<h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-blue-500 text-center mb-4 sm:mb-6">About Our Project</h1>
<div id="about-container" class="bg-gray-800 p-4 sm:p-6 lg:p-8 rounded-lg shadow-xl max-w-4xl mx-auto min-w-0">
    <section class="mb-6 sm:mb-8">
        <h2 class="text-xl sm:text-2xl lg:text-3xl font-bold text-white mb-3 sm:mb-4 border-b-2 border-blue-500 pb-2">Project Overview</h2>
        <p class="text-base sm:text-lg text-gray-300 leading-relaxed break-anywhere">
            This website is a college project developed to provide a seamless audiobook streaming and content management experience. Our goal was to create a functional, responsive, and engaging web platform that allows users to explore and listen to audiobooks easily while giving administrators complete control over content management.
        </p>
    </section>

    <section class="mb-6 sm:mb-8">
        <h2 class="text-xl sm:text-2xl lg:text-3xl font-bold text-white mb-3 sm:mb-4 border-b-2 border-blue-500 pb-2">Platform Features</h2>
        <p class="text-base sm:text-lg text-gray-300 leading-relaxed mb-4 sm:mb-6 break-anywhere">
            StoryVerse provides a simple, focused audiobook platform where users can browse, listen to, and manage audiobooks. The site is designed to be accessible and easy to use for both listeners and administrators.
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 text-center">
            <div class="bg-gray-700 p-4 sm:p-6 rounded-lg shadow-md min-w-0">
                <i class="fas fa-book text-blue-400 text-4xl mb-3"></i>
                <h3 class="text-xl font-semibold text-white mb-2">Browse & Listen</h3>
                <p class="text-gray-400">Search the catalog and play audiobook parts directly in your browser.</p>
            </div>
            <div class="bg-gray-700 p-4 sm:p-6 rounded-lg shadow-md min-w-0">
                <i class="fas fa-user text-blue-400 text-4xl mb-3"></i>
                <h3 class="text-xl font-semibold text-white mb-2">User Profiles</h3>
                <p class="text-gray-400">Manage your profile and view your messages and activity.</p>
            </div>
            <div class="bg-gray-700 p-4 sm:p-6 rounded-lg shadow-md min-w-0">
                <i class="fas fa-upload text-blue-400 text-4xl mb-3"></i>
                <h3 class="text-xl font-semibold text-white mb-2">Admin Management</h3>
                <p class="text-gray-400">Administrators can add audiobooks, upload parts, and manage users.</p>
            </div>
            <div class="bg-gray-700 p-4 sm:p-6 rounded-lg shadow-md min-w-0">
                <i class="fas fa-lock text-blue-400 text-4xl mb-3"></i>
                <h3 class="text-xl font-semibold text-white mb-2">Privacy & Control</h3>
                <p class="text-gray-400">Users control their profiles and admins maintain moderation tools.</p>
            </div>
        </div>
    </section>

    <section class="mt-6 sm:mt-8">
        <h2 class="text-xl sm:text-2xl lg:text-3xl font-bold text-white mb-3 sm:mb-4 border-b-2 border-blue-500 pb-2">About the Team</h2>
        <p class="text-base sm:text-lg text-gray-300 leading-relaxed break-anywhere">
            This project was created by a team of enthusiastic college students who are passionate about technology and its potential to solve real-world problems. We hope this project demonstrates our skills in web development and front-end design.
        </p>
    </section>
</div>



<?php require_once 'includes/footer.php'; ?>
