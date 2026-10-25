<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hapus semua data sesi
$_SESSION = [];
session_unset();
session_destroy();

// Alihkan kembali ke halaman login
header('Location: login.php');
exit;