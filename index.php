<?php
// FILE: index.php
// This is the initial landing page. If the user is not logged in, it redirects them to the new single login page.
// If a user is logged in, it redirects them to the appropriate page based on their role.

session_start();

// Check if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Redirect to the appropriate panel based on the user's role
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    // Redirect to the admin profile for administrators
    header("Location: admin_profile.php");
    exit;
} else {
    // Redirect to the profile page for regular users
    header("Location: profile.php");
    exit;
}