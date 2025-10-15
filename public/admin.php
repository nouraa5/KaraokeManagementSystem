<?php
// admin.php – central entry for admin users (redirects to queue)
// -----------------------------------------------------------------------------
// Make sure this file *begins* with the opening PHP tag; otherwise the server
// will just send the code as plain text and you’ll see a blank page.

// -- error reporting (comment out in production) -----------------------------
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// -- includes & session ------------------------------------------------------
// Start session once (safe for pages that already called session_start)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/functions.php';

// -- authentication gate -----------------------------------------------------
if (empty($_SESSION['is_admin'])) {
    header('Location: login.php');
    exit;
}

// -- keep ?table=... param if present ---------------------------------------
$tableParam = isset($_GET['table']) ? 'table=' . urlencode($_GET['table']) : '';

// Absolute redirect avoids relative‑path surprises
$dest = 'queue.php' . ($tableParam ? "?$tableParam" : '');
header("Location: $dest");
exit;
