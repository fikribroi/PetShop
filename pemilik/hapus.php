<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

checkAuth();

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    // Ambil nama pemilik untuk pesan notifikasi
    $stmtFind = mysqli_prepare($conn, "SELECT nama_pemilik FROM pemilik WHERE id_pemilik = ? LIMIT 1");
    mysqli_stmt_bind_param($stmtFind, "i", $id);
    mysqli_stmt_execute($stmtFind);
    $resFind = mysqli_stmt_get_result($stmtFind);
    $data = mysqli_fetch_assoc($resFind);
    mysqli_stmt_close($stmtFind);

    if ($data) {
        $nama = $data['nama_pemilik'];
        // Coba hapus
        try {
            $stmtDel = mysqli_prepare($conn, "DELETE FROM pemilik WHERE id_pemilik = ?");
            mysqli_stmt_bind_param($stmtDel, "i", $id);
            if (mysqli_stmt_execute($stmtDel)) {
                setFlashMessage('success', "Data pemilik '$nama' beserta hewan peliharaannya berhasil dihapus!");
            } else {
                setFlashMessage('danger', "Gagal menghapus data pemilik: " . mysqli_error($conn));
            }
            mysqli_stmt_close($stmtDel);
        } catch (mysqli_sql_exception $e) {
            // Tangkap Foreign Key Constraint Violated jika ada transaksi penitipan terkait
            setFlashMessage('danger', "Tidak dapat menghapus pemilik '$nama'! Hewan milik pelanggan ini masih terdaftar dalam transaksi penitipan (Constraint Foreign Key RESTRICT). Hapus terlebih dahulu transaksi terkait.");
        }
    } else {
        setFlashMessage('warning', "Data pemilik tidak ditemukan!");
    }
} else {
    setFlashMessage('danger', "ID Pemilik tidak valid!");
}

header("Location: " . base_url('pemilik/index.php'));
exit;

