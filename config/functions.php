<?php
// ==============================================================
// HELPER FUNCTIONS SISTEM PET SHOP
// ==============================================================

// Deteksi otomatis base URL proyek
function base_url($path = '') {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    // Cari root direktori proyek jika berada di dalam subfolder
    $subfolders = ['/pemilik', '/hewan', '/kandang', '/layanan', '/penitipan', '/auth'];
    $projectRoot = $scriptDir;
    foreach ($subfolders as $sub) {
        if (str_ends_with($projectRoot, $sub)) {
            $projectRoot = substr($projectRoot, 0, -strlen($sub));
            break;
        }
    }
    $projectRoot = rtrim($projectRoot, '/');
    $cleanPath = ltrim($path, '/');
    return ($cleanPath === '') ? $projectRoot : $projectRoot . '/' . $cleanPath;
}

// Format Angka ke Rupiah (Rp. xx.xxx)
function formatRupiah($angka) {
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}

// Format Tanggal Indonesia
function formatTanggalIndo($tanggal) {
    if (!$tanggal || $tanggal === '0000-00-00') return '-';
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $pecahkan = explode('-', date('Y-m-d', strtotime($tanggal)));
    return $pecahkan[2] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
}

// Sanitasi Input Form (Cegah XSS & SQL Injection dasar)
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Helper Flash Message (Notifikasi sukses / gagal antar halaman)
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type'    => $type, // 'success', 'danger', 'warning', 'info'
        'message' => $message
    ];
}

function showFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        $icon = match($flash['type']) {
            'success' => 'bi-check-circle-fill',
            'danger'  => 'bi-exclamation-triangle-fill',
            'warning' => 'bi-exclamation-circle-fill',
            default   => 'bi-info-circle-fill'
        };
        echo '
        <div class="alert alert-' . htmlspecialchars($flash['type']) . ' alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
            <i class="bi ' . $icon . ' fs-5 me-2"></i>
            <div>' . htmlspecialchars($flash['message']) . '</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    }
}

// Cek status autentikasi user
function checkAuth() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . base_url('auth/login.php'));
        exit;
    }
}

// Generate Kode Transaksi Unik Otomatis (misal TRX-20261103-011)
function generateKodeTransaksi($conn) {
    $today = date('Ymd');
    $prefix = "TRX-" . $today . "-";
    $query = "SELECT kode_transaksi FROM penitipan WHERE kode_transaksi LIKE '$prefix%' ORDER BY id_penitipan DESC LIMIT 1";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $lastCode = $row['kode_transaksi'];
        $lastNum = (int)substr($lastCode, -3);
        $nextNum = str_pad($lastNum + 1, 3, '0', STR_PAD_LEFT);
    } else {
        $nextNum = "001";
    }
    return $prefix . $nextNum;
}

