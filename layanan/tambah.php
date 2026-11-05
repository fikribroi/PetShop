<?php
$pageTitle = 'Tambah Layanan Baru';
$pageSubtitle = 'Input data paket layanan pet shop & perawatan';
require_once __DIR__ . '/../includes/header.php';

$errors = [];
$nama_layanan  = cleanInput($_POST['nama_layanan'] ?? '');
$kategori      = cleanInput($_POST['kategori'] ?? 'Grooming & Spa');
$tarif_layanan = cleanInput($_POST['tarif_layanan'] ?? '');
$deskripsi     = cleanInput($_POST['deskripsi'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($nama_layanan)) $errors[] = "Nama layanan wajib diisi!";
    if ($tarif_layanan === '' || !is_numeric($tarif_layanan) || (float)$tarif_layanan < 0) {
        $errors[] = "Tarif layanan harus berupa angka 0 atau lebih!";
    }

    if (empty($errors)) {
        $sql = "INSERT INTO layanan (nama_layanan, kategori, tarif_layanan, deskripsi) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        $tarif = (float)$tarif_layanan;
        mysqli_stmt_bind_param($stmt, "ssds", $nama_layanan, $kategori, $tarif, $deskripsi);

        if (mysqli_stmt_execute($stmt)) {
            setFlashMessage('success', "Layanan '$nama_layanan' berhasil ditambahkan!");
            header("Location: " . base_url('layanan/index.php'));
            exit;
        } else {
            $errors[] = "Gagal menyimpan data: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-custom">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-card-checklist me-2 text-primary"></i>Form Tambah Layanan</span>
                <a href="<?= base_url('layanan/index.php') ?>" class="btn btn-sm btn-outline-secondary">
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
                        <label for="nama_layanan" class="form-label fw-semibold">Nama Layanan / Paket <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_layanan" name="nama_layanan" value="<?= htmlspecialchars($nama_layanan) ?>" placeholder="Contoh: Grooming Mandi Sehat Reguler" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="kategori" class="form-label fw-semibold">Kategori Layanan <span class="text-danger">*</span></label>
                            <select name="kategori" id="kategori" class="form-select" required>
                                <option value="Penitipan Saja" <?= $kategori === 'Penitipan Saja' ? 'selected' : '' ?>>Penitipan Saja</option>
                                <option value="Grooming & Spa" <?= $kategori === 'Grooming & Spa' ? 'selected' : '' ?>>Grooming & Spa</option>
                                <option value="Kesehatan & Vaksin" <?= $kategori === 'Kesehatan & Vaksin' ? 'selected' : '' ?>>Kesehatan & Vaksin</option>
                                <option value="Perawatan Khusus" <?= $kategori === 'Perawatan Khusus' ? 'selected' : '' ?>>Perawatan Khusus</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tarif_layanan" class="form-label fw-semibold">Tarif Biaya (Rp) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="tarif_layanan" name="tarif_layanan" min="0" step="500" value="<?= htmlspecialchars($tarif_layanan) ?>" placeholder="Contoh: 50000 (Isi 0 jika include)" required>
                            <small class="text-muted">Isi 0 jika layanan sudah termasuk dalam paket dasar.</small>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="deskripsi" class="form-label fw-semibold">Deskripsi / Rincian Layanan</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" placeholder="Jelaskan fasilitas atau tahapan yang didapat hewan..."><?= htmlspecialchars($deskripsi) ?></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= base_url('layanan/index.php') ?>" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Simpan Data Layanan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

