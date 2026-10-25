<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Jika sudah login, masuk ke dashboard. Jika belum, masuk ke login.php
if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
} else {
    header('Location: login.php');
}
exit;