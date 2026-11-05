<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hapus semua data sesi
$_SESSION = [];
session_unset();
session_destroy();

// Mulai sesi baru untuk pesan flash
session_start();
setFlashMessage('info', 'Anda telah berhasil logout.');

header("Location: " . base_url('auth/login.php'));
exit;

