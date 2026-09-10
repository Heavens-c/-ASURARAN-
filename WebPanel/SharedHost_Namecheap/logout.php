<?php
/**
 * RanOnline Web Panel — Logout
 */
require_once __DIR__ . '/includes/auth.php';

Auth::logout();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['flash_success'] = 'You have been successfully logged out.';
header('Location: login.php');
exit;
