<?php
// admin/logout.php — Secure session destruction
require_once __DIR__ . '/../includes/security.php';
secure_session_start();

// Log the logout event
require_once __DIR__ . '/../includes/functions.php';
log_security_event('ADMIN_LOGOUT', ['email' => $_SESSION['admin_name'] ?? 'unknown']);

// Destroy all session data
$_SESSION = [];

// Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Security: Clear any output buffering
if (ob_get_level()) {
    ob_end_clean();
}

// Redirect with no-cache headers
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
header("Location: login.php");
exit();
