<?php
// FILE: logout.php
// Logs the user out by destroying the session and redirects.

// Set headers to prevent caching of the page.
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

session_start();

// Unset all of the session variables.
$_SESSION = array();
// If it's desired to kill the session, also delete the session cookie.
if (ini_get("session.use_cookies")) {
	$params = session_get_cookie_params();
	setcookie(session_name(), '', time() - 42000,
		$params["path"], $params["domain"],
		$params["secure"], $params["httponly"]
	);
}
// Destroy the session.
session_destroy();

// Redirect to the index page.
header("Location: login.php?logout_success=1");
exit;
?>