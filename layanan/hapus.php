<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

checkAuth();

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    $stmtFind = mysqli_prepare($conn, "SELECT nama_layanan FROM layanan WHERE id_layanan = ? LIMIT 1");
    mysqli_stmt_bind_param($stmtFind, "i", $id);
    mysqli_stmt_execute($stmtFind);
    $resFind = mysqli_stmt_get_result($stmtFind);
    $data = mysqli_fetch_assoc($resFind);
    mysqli_stmt_close($stmtFind);

    if ($data) {
        $nama = $data['nama_layanan'];
        try {
            $stmtDel = mysqli_prepare($conn, "DELETE FROM layanan WHERE id_layanan = ?");
            mysqli_stmt_bind_param($stmtDel, "i", $id);
            if (mysqli_stmt_execute($stmtDel)) {
                setFlashMessage('success', "Layanan '$nama' berhasil dihapus!");
            } else {
                setFlashMessage('danger', "Gagal menghapus layanan: " . mysqli_error($conn));
            }
            mysqli_stmt_close($stmtDel);
        } catch (mysqli_sql_exception $e) {
            setFlashMessage('danger', "Tidak dapat menghapus layanan '$nama'! Layanan ini masih tercatat dalam transaksi penitipan (Constraint Foreign Key RESTRICT).");
        }
    } else {
        setFlashMessage('warning', "Data layanan tidak ditemukan!");
    }
} else {
    setFlashMessage('danger', "ID Layanan tidak valid!");
}

header("Location: " . base_url('layanan/index.php'));
exit;

