<?php
$pageTitle = 'Edit Data Pemilik';
$pageSubtitle = 'Perbarui data pelanggan pemilik hewan';
require_once __DIR__ . '/../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    setFlashMessage('danger', 'ID Pemilik tidak valid!');
    header("Location: " . base_url('pemilik/index.php'));
    exit;
}

// Ambil data pemilik saat ini
$stmt = mysqli_prepare($conn, "SELECT * FROM pemilik WHERE id_pemilik = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$pemilik = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$pemilik) {
    setFlashMessage('danger', 'Data pemilik tidak ditemukan!');
    header("Location: " . base_url('pemilik/index.php'));
    exit;
}

$errors = [];
$nama_pemilik = $pemilik['nama_pemilik'];
$no_hp        = $pemilik['no_hp'];
$email        = $pemilik['email'];
$alamat       = $pemilik['alamat'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_pemilik = cleanInput($_POST['nama_pemilik'] ?? '');
    $no_hp        = cleanInput($_POST['no_hp'] ?? '');
    $email        = cleanInput($_POST['email'] ?? '');
    $alamat       = cleanInput($_POST['alamat'] ?? '');

    if (empty($nama_pemilik)) $errors[] = "Nama pemilik wajib diisi!";
    if (empty($no_hp)) $errors[] = "Nomor HP wajib diisi!";
    if (empty($alamat)) $errors[] = "Alamat wajib diisi!";

    // Cek duplikasi email ke ID lain jika diisi
    if (!empty($email)) {
        $stmtCheck = mysqli_prepare($conn, "SELECT id_pemilik FROM pemilik WHERE email = ? AND id_pemilik != ? LIMIT 1");
        mysqli_stmt_bind_param($stmtCheck, "si", $email, $id);
        mysqli_stmt_execute($stmtCheck);
        $resCheck = mysqli_stmt_get_result($stmtCheck);
        if (mysqli_num_rows($resCheck) > 0) {
            $errors[] = "Email '$email' sudah digunakan oleh pemilik lain!";
        }
        mysqli_stmt_close($stmtCheck);
    }

    if (empty($errors)) {
        $updateQuery = "UPDATE pemilik SET nama_pemilik = ?, no_hp = ?, email = ?, alamat = ? WHERE id_pemilik = ?";
        $stmtUpdate = mysqli_prepare($conn, $updateQuery);
        mysqli_stmt_bind_param($stmtUpdate, "ssssi", $nama_pemilik, $no_hp, $email, $alamat, $id);

        if (mysqli_stmt_execute($stmtUpdate)) {
            setFlashMessage('success', "Data pemilik '$nama_pemilik' berhasil diperbarui!");
            header("Location: " . base_url('pemilik/index.php'));
            exit;
        } else {
            $errors[] = "Terjadi kesalahan database: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmtUpdate);
    }
}
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-custom">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-pencil-square me-2 text-warning"></i>Form Edit Pemilik</span>
                <a href="<?= base_url('pemilik/index.php') ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <div class="card-body p-4">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $err): ?>
                                <li><?= htmlspecialchars($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form action="" method="POST">
                    <div class="mb-3">
                        <label for="nama_pemilik" class="form-label fw-semibold">Nama Lengkap Pemilik <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_pemilik" name="nama_pemilik" value="<?= htmlspecialchars($nama_pemilik) ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="no_hp" class="form-label fw-semibold">Nomor HP / WhatsApp <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="no_hp" name="no_hp" value="<?= htmlspecialchars($no_hp) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label fw-semibold">Alamat Email <span class="text-muted small">(Opsional)</span></label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email) ?>">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="alamat" class="form-label fw-semibold">Alamat Lengkap <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="3" required><?= htmlspecialchars($alamat) ?></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= base_url('pemilik/index.php') ?>" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-warning text-white">
                            <i class="bi bi-check2-circle me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

