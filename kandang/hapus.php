<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

checkAuth();

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    $stmtFind = mysqli_prepare($conn, "SELECT nomor_kandang FROM kandang WHERE id_kandang = ? LIMIT 1");
    mysqli_stmt_bind_param($stmtFind, "i", $id);
    mysqli_stmt_execute($stmtFind);
    $resFind = mysqli_stmt_get_result($stmtFind);
    $data = mysqli_fetch_assoc($resFind);
    mysqli_stmt_close($stmtFind);

    if ($data) {
        $nomor = $data['nomor_kandang'];
        try {
            $stmtDel = mysqli_prepare($conn, "DELETE FROM kandang WHERE id_kandang = ?");
            mysqli_stmt_bind_param($stmtDel, "i", $id);
            if (mysqli_stmt_execute($stmtDel)) {
                setFlashMessage('success', "Kandang '$nomor' berhasil dihapus!");
            } else {
                setFlashMessage('danger', "Gagal menghapus kandang: " . mysqli_error($conn));
            }
            mysqli_stmt_close($stmtDel);
        } catch (mysqli_sql_exception $e) {
            setFlashMessage('danger', "Tidak dapat menghapus kandang '$nomor'! Kandang ini masih tercatat dalam transaksi penitipan (Constraint Foreign Key RESTRICT).");
        }
    } else {
        setFlashMessage('warning', "Data kandang tidak ditemukan!");
    }
} else {
    setFlashMessage('danger', "ID Kandang tidak valid!");
}

header("Location: " . base_url('kandang/index.php'));
exit;

