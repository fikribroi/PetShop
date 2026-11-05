<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

checkAuth();

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    // Ambil info transaksi untuk pesan dan kandang
    $stmtFind = mysqli_prepare($conn, "SELECT kode_transaksi, id_kandang, status_penitipan FROM penitipan WHERE id_penitipan = ? LIMIT 1");
    mysqli_stmt_bind_param($stmtFind, "i", $id);
    mysqli_stmt_execute($stmtFind);
    $resFind = mysqli_stmt_get_result($stmtFind);
    $data = mysqli_fetch_assoc($resFind);
    mysqli_stmt_close($stmtFind);

    if ($data) {
        $kode = $data['kode_transaksi'];
        $idKandang = $data['id_kandang'];

        $stmtDel = mysqli_prepare($conn, "DELETE FROM penitipan WHERE id_penitipan = ?");
        mysqli_stmt_bind_param($stmtDel, "i", $id);

        if (mysqli_stmt_execute($stmtDel)) {
            // Update status kandang kembali jadi 'Tersedia' jika tidak ada transaksi aktif lain di kandang tersebut
            $cekAktif = mysqli_query($conn, "SELECT id_penitipan FROM penitipan WHERE id_kandang = $idKandang AND status_penitipan = 'Aktif' LIMIT 1");
            if (mysqli_num_rows($cekAktif) === 0) {
                mysqli_query($conn, "UPDATE kandang SET status_kandang = 'Tersedia' WHERE id_kandang = $idKandang");
            }

            setFlashMessage('success', "Transaksi penitipan '$kode' berhasil dihapus!");
        } else {
            setFlashMessage('danger', "Gagal menghapus transaksi: " . mysqli_error($conn));
        }
        mysqli_stmt_close($stmtDel);
    } else {
        setFlashMessage('warning', "Data transaksi tidak ditemukan!");
    }
} else {
    setFlashMessage('danger', "ID Transaksi tidak valid!");
}

header("Location: " . base_url('penitipan/index.php'));
exit;

