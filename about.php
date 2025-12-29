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

<h1 class="text-4xl font-bold text-blue-500 text-center mb-6">About Our Project</h1>
<div id="about-container" class="bg-gray-800 p-8 rounded-lg shadow-xl max-w-4xl mx-auto">
    <section class="mb-8">
        <h2 class="text-3xl font-bold text-white mb-4 border-b-2 border-blue-500 pb-2">Project Overview</h2>
        <p class="text-lg text-gray-300 leading-relaxed">
            This website is a college project developed to demonstrate the integration of various AI-powered features for a better user experience. Our goal was to create a functional and engaging platform that showcases how modern web technologies can be used to process and deliver information in new and innovative ways.
        </p>
    </section>

    <section class="mb-8">
        <h2 class="text-3xl font-bold text-white mb-4 border-b-2 border-blue-500 pb-2">Our Features</h2>
        <p class="text-lg text-gray-300 leading-relaxed mb-6">
            The core of this project is the **AI Studio**, which includes several tools built to transform text, images, and documents into accessible formats.
        </p>
        <div class="grid md:grid-cols-2 gap-6 text-center">
            <div class="bg-gray-700 p-6 rounded-lg shadow-md">
                <i class="fas fa-file-alt text-blue-400 text-4xl mb-3"></i>
                <h3 class="text-xl font-semibold text-white mb-2">Text-to-Speech</h3>
                <p class="text-gray-400">Convert any written text into natural-sounding audio.</p>
            </div>
            <div class="bg-gray-700 p-6 rounded-lg shadow-md">
                <i class="fas fa-image text-blue-400 text-4xl mb-3"></i>
                <h3 class="text-xl font-semibold text-white mb-2">Image-to-Speech</h3>
                <p class="text-gray-400">Extract text from images and have it read aloud.</p>
            </div>
            <div class="bg-gray-700 p-6 rounded-lg shadow-md">
                <i class="fas fa-file-pdf text-blue-400 text-4xl mb-3"></i>
                <h3 class="text-xl font-semibold text-white mb-2">PDF-to-Speech</h3>
                <p class="text-gray-400">Read PDF documents aloud, making them easy to consume.</p>
            </div>
            <div class="bg-gray-700 p-6 rounded-lg shadow-md">
                <i class="fas fa-compress-alt text-blue-400 text-4xl mb-3"></i>
                <h3 class="text-xl font-semibold text-white mb-2">Text Summarizer</h3>
                <p class="text-gray-400">Quickly summarize long passages to get the key points.</p>
            </div>
        </div>
    </section>

    <section class="text-center mt-8">
        <p class="text-lg text-gray-300 leading-relaxed mb-4">
            Ready to try our AI-powered tools?
        </p>
        <a href="ai.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-full transition-colors duration-300 inline-flex items-center space-x-2">
            <i class="fas fa-magic"></i>
            <span>Visit AI Features</span>
        </a>
    </section>

    <section class="mt-8">
        <h2 class="text-3xl font-bold text-white mb-4 border-b-2 border-blue-500 pb-2">About the Team</h2>
        <p class="text-lg text-gray-300 leading-relaxed">
            This project was created by a team of enthusiastic college students who are passionate about technology and its potential to solve real-world problems. We hope this project demonstrates our skills in web development, front-end design, and API integration.
        </p>
    </section>
</div>



<?php require_once 'includes/footer.php'; ?>
