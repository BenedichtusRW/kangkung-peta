<?php
/**
 * admin/includes/auth.php
 * Session guard. Include di paling atas setiap halaman admin yang
 * butuh login (kecuali login.php sendiri).
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}
