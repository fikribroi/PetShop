<?php
$pageTitle = 'Tambah Kandang Baru';
$pageSubtitle = 'Input fasilitas kandang penitipan hewan baru';
require_once __DIR__ . '/../includes/header.php';

$errors = [];
$nomor_kandang  = cleanInput($_POST['nomor_kandang'] ?? '');
$tipe_kandang   = cleanInput($_POST['tipe_kandang'] ?? 'Standar');
$kapasitas      = cleanInput($_POST['kapasitas'] ?? '1');
$tarif_per_hari = cleanInput($_POST['tarif_per_hari'] ?? '');
$status_kandang = cleanInput($_POST['status_kandang'] ?? 'Tersedia');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($nomor_kandang)) $errors[] = "Nomor kandang wajib diisi!";
    if (empty($kapasitas) || !is_numeric($kapasitas) || (int)$kapasitas <= 0) {
        $errors[] = "Kapasitas kandang harus lebih dari 0!";
    }
    if (empty($tarif_per_hari) || !is_numeric($tarif_per_hari) || (float)$tarif_per_hari <= 0) {
        $errors[] = "Tarif per hari harus berupa angka positif!";
    }

    // Cek Unique Constraint nomor_kandang
    if (!empty($nomor_kandang)) {
        $stmtCheck = mysqli_prepare($conn, "SELECT id_kandang FROM kandang WHERE nomor_kandang = ? LIMIT 1");
        mysqli_stmt_bind_param($stmtCheck, "s", $nomor_kandang);
        mysqli_stmt_execute($stmtCheck);
        $resCheck = mysqli_stmt_get_result($stmtCheck);
        if (mysqli_num_rows($resCheck) > 0) {
            $errors[] = "Nomor kandang '$nomor_kandang' sudah terdaftar dalam sistem!";
        }
        mysqli_stmt_close($stmtCheck);
    }

    if (empty($errors)) {
        $sql = "INSERT INTO kandang (nomor_kandang, tipe_kandang, kapasitas, tarif_per_hari, status_kandang) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        $kap = (int)$kapasitas;
        $tarif = (float)$tarif_per_hari;
        mysqli_stmt_bind_param($stmt, "ssids", $nomor_kandang, $tipe_kandang, $kap, $tarif, $status_kandang);

        if (mysqli_stmt_execute($stmt)) {
            setFlashMessage('success', "Kandang '$nomor_kandang' berhasil ditambahkan!");
            header("Location: " . base_url('kandang/index.php'));
            exit;
        } else {
            $errors[] = "Gagal menyimpan ke database: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-custom">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-box-seam me-2 text-primary"></i>Form Tambah Kandang</span>
                <a href="<?= base_url('kandang/index.php') ?>" class="btn btn-sm btn-outline-secondary">
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
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nomor_kandang" class="form-label fw-semibold">Nomor / Kode Kandang <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nomor_kandang" name="nomor_kandang" value="<?= htmlspecialchars($nomor_kandang) ?>" placeholder="Contoh: K-11" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tipe_kandang" class="form-label fw-semibold">Tipe Ruangan <span class="text-danger">*</span></label>
                            <select name="tipe_kandang" id="tipe_kandang" class="form-select" required>
                                <option value="Standar" <?= $tipe_kandang === 'Standar' ? 'selected' : '' ?>>Standar (Ventilasi Nyaman)</option>
                                <option value="VIP" <?= $tipe_kandang === 'VIP' ? 'selected' : '' ?>>VIP (Ruangan AC + Play Area)</option>
                                <option value="VVIP" <?= $tipe_kandang === 'VVIP' ? 'selected' : '' ?>>VVIP (Kamar Privat AC + CCTV)</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="kapasitas" class="form-label fw-semibold">Kapasitas (Ekor) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="kapasitas" name="kapasitas" min="1" value="<?= htmlspecialchars($kapasitas) ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="tarif_per_hari" class="form-label fw-semibold">Tarif / Hari (Rp) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="tarif_per_hari" name="tarif_per_hari" min="1000" step="500" value="<?= htmlspecialchars($tarif_per_hari) ?>" placeholder="Contoh: 50000" required>
                        </div>
                        <div class="col-md-4 mb-4">
                            <label for="status_kandang" class="form-label fw-semibold">Status Awal <span class="text-danger">*</span></label>
                            <select name="status_kandang" id="status_kandang" class="form-select" required>
                                <option value="Tersedia" <?= $status_kandang === 'Tersedia' ? 'selected' : '' ?>>Tersedia</option>
                                <option value="Terisi" <?= $status_kandang === 'Terisi' ? 'selected' : '' ?>>Terisi</option>
                                <option value="Perbaikan" <?= $status_kandang === 'Perbaikan' ? 'selected' : '' ?>>Perbaikan</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= base_url('kandang/index.php') ?>" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Simpan Data Kandang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

