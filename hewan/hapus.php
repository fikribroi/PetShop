<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

checkAuth();

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    $stmtFind = mysqli_prepare($conn, "SELECT nama_hewan FROM hewan WHERE id_hewan = ? LIMIT 1");
    mysqli_stmt_bind_param($stmtFind, "i", $id);
    mysqli_stmt_execute($stmtFind);
    $resFind = mysqli_stmt_get_result($stmtFind);
    $data = mysqli_fetch_assoc($resFind);
    mysqli_stmt_close($stmtFind);

    if ($data) {
        $nama = $data['nama_hewan'];
        try {
            $stmtDel = mysqli_prepare($conn, "DELETE FROM hewan WHERE id_hewan = ?");
            mysqli_stmt_bind_param($stmtDel, "i", $id);
            if (mysqli_stmt_execute($stmtDel)) {
                setFlashMessage('success', "Data hewan '$nama' berhasil dihapus!");
            } else {
                setFlashMessage('danger', "Gagal menghapus data hewan: " . mysqli_error($conn));
            }
            mysqli_stmt_close($stmtDel);
        } catch (mysqli_sql_exception $e) {
            setFlashMessage('danger', "Tidak dapat menghapus hewan '$nama'! Hewan ini masih terikat dengan riwayat transaksi penitipan (Constraint Foreign Key RESTRICT). Hapus terlebih dahulu transaksi terkait.");
        }
    } else {
        setFlashMessage('warning', "Data hewan tidak ditemukan!");
    }
} else {
    setFlashMessage('danger', "ID Hewan tidak valid!");
}

header("Location: " . base_url('hewan/index.php'));
exit;

