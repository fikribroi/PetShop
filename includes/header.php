<?php
// Pastikan koneksi database dan helper tersedia
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

// Pastikan user sudah login
checkAuth();

// Dapatkan nama skrip saat ini untuk menu aktif
$currentScript = $_SERVER['SCRIPT_NAME'];
function isMenuActive($segment) {
    global $currentScript;
    if ($segment === 'dashboard' && (str_ends_with($currentScript, 'index.php') && !str_contains($currentScript, 'pemilik') && !str_contains($currentScript, 'hewan') && !str_contains($currentScript, 'kandang') && !str_contains($currentScript, 'layanan') && !str_contains($currentScript, 'penitipan'))) {
        return 'active';
    }
    return str_contains($currentScript, '/' . $segment . '/') ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : '' ?>PetCare Pet Shop & Penitipan</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom Style -->
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body>

<div class="d-flex">
    <!-- SIDEBAR -->
    <aside id="sidebar" class="d-flex flex-column flex-shrink-0 p-0 shadow-sm">
        <a href="<?= base_url('index.php') ?>" class="brand">
            <i class="bi bi-shield-heart-fill"></i>
            <div>
                <div class="lh-1">PetCare Studio</div>
                <small class="text-white-50" style="font-size: 0.75rem; font-weight: normal;">Pet Shop & Penitipan</small>
            </div>
        </a>

        <div class="sidebar-heading">Menu Utama</div>
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a href="<?= base_url('index.php') ?>" class="nav-link <?= isMenuActive('dashboard') ?>">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <div class="sidebar-heading">Master Data (5 Tabel)</div>
            <li class="nav-item">
                <a href="<?= base_url('pemilik/index.php') ?>" class="nav-link <?= isMenuActive('pemilik') ?>">
                    <i class="bi bi-people-fill"></i>
                    <span>1. Data Pemilik</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('hewan/index.php') ?>" class="nav-link <?= isMenuActive('hewan') ?>">
                    <i class="bi bi-github"></i>
                    <span>2. Data Hewan</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('kandang/index.php') ?>" class="nav-link <?= isMenuActive('kandang') ?>">
                    <i class="bi bi-box-seam-fill"></i>
                    <span>3. Data Kandang</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('layanan/index.php') ?>" class="nav-link <?= isMenuActive('layanan') ?>">
                    <i class="bi bi-card-checklist"></i>
                    <span>4. Data Layanan</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('penitipan/index.php') ?>" class="nav-link <?= isMenuActive('penitipan') ?>">
                    <i class="bi bi-calendar2-heart-fill"></i>
                    <span>5. Transaksi Penitipan</span>
                </a>
            </li>
        </ul>

        <hr class="text-white-50 my-2 mx-3">
        <div class="px-3 pb-3">
            <div class="d-flex align-items-center text-white p-2 rounded" style="background-color: rgba(255,255,255,0.05);">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 36px; height: 36px;">
                    <i class="bi bi-person-fill"></i>
                </div>
                <div class="overflow-hidden lh-1">
                    <strong class="d-block text-truncate" style="font-size: 0.85rem;"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Petugas') ?></strong>
                    <small class="text-white-50" style="font-size: 0.72rem;"><?= htmlspecialchars($_SESSION['role'] ?? 'Staff') ?></small>
                </div>
                <a href="<?= base_url('auth/logout.php') ?>" class="btn btn-sm btn-outline-danger ms-auto p-1 px-2" title="Logout" onclick="return confirm('Apakah Anda yakin ingin logout?')">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>
    </aside>

    <!-- CONTENT WRAPPER -->
    <div id="content">
        <!-- TOP NAVBAR -->
        <header class="top-navbar d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-light d-md-none border" id="sidebarToggle" type="button">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div class="d-none d-sm-block">
                    <h5 class="mb-0 fw-bold"><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Dashboard' ?></h5>
                    <small class="text-muted"><?= isset($pageSubtitle) ? htmlspecialchars($pageSubtitle) : 'Sistem Informasi Pet Shop & Penitipan Hewan' ?></small>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-light text-dark border px-3 py-2 d-none d-md-inline-block">
                    <i class="bi bi-clock me-1 text-primary"></i> <?= date('d M Y') ?>
                </span>
                <a href="<?= base_url('penitipan/tambah.php') ?>" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Transaksi Baru
                </a>
            </div>
        </header>

        <!-- MAIN CONTAINER -->
        <main class="container-fluid p-4">
            <!-- Flash Message Alert -->
            <?php showFlashMessage(); ?>

