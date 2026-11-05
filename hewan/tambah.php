<?php
$pageTitle = 'Tambah Hewan Peliharaan';
$pageSubtitle = 'Input data hewan baru berelasi dengan data pemilik';
require_once __DIR__ . '/../includes/header.php';

// Ambil daftar seluruh pemilik untuk pilihan dropdown Foreign Key
$resPemilik = mysqli_query($conn, "SELECT id_pemilik, nama_pemilik, no_hp FROM pemilik ORDER BY nama_pemilik ASC");

$errors = [];
$id_pemilik    = (int)($_POST['id_pemilik'] ?? 0);
$nama_hewan    = cleanInput($_POST['nama_hewan'] ?? '');
$jenis_hewan   = cleanInput($_POST['jenis_hewan'] ?? 'Kucing');
$ras           = cleanInput($_POST['ras'] ?? '');
$umur_bulan    = cleanInput($_POST['umur_bulan'] ?? '');
$jenis_kelamin = cleanInput($_POST['jenis_kelamin'] ?? 'Jantan');
$berat_kg      = cleanInput($_POST['berat_kg'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi
    if ($id_pemilik <= 0) $errors[] = "Silakan pilih pemilik hewan!";
    if (empty($nama_hewan)) $errors[] = "Nama hewan wajib diisi!";
    if (empty($ras)) $errors[] = "Ras / jenis spesifik hewan wajib diisi!";
    if ($umur_bulan === '' || !is_numeric($umur_bulan) || (int)$umur_bulan < 0) {
        $errors[] = "Umur hewan harus berupa angka positif (dalam bulan)!";
    }
    if ($berat_kg === '' || !is_numeric($berat_kg) || (float)$berat_kg <= 0) {
        $errors[] = "Berat hewan harus berupa angka desimal lebih dari 0 kg!";
    }

    if (empty($errors)) {
        $sql = "INSERT INTO hewan (id_pemilik, nama_hewan, jenis_hewan, ras, umur_bulan, jenis_kelamin, berat_kg) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        $umur = (int)$umur_bulan;
        $berat = (float)$berat_kg;
        mysqli_stmt_bind_param($stmt, "isssisd", $id_pemilik, $nama_hewan, $jenis_hewan, $ras, $umur, $jenis_kelamin, $berat);

        if (mysqli_stmt_execute($stmt)) {
            setFlashMessage('success', "Hewan '$nama_hewan' berhasil didaftarkan ke sistem!");
            header("Location: " . base_url('hewan/index.php'));
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
                <span class="fw-bold"><i class="bi bi-plus-circle me-2 text-primary"></i>Form Registrasi Hewan Peliharaan</span>
                <a href="<?= base_url('hewan/index.php') ?>" class="btn btn-sm btn-outline-secondary">
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
                    <!-- Dropdown Foreign Key Pemilik -->
                    <div class="mb-3">
                        <label for="id_pemilik" class="form-label fw-semibold">Pilih Pemilik Hewan (Foreign Key) <span class="text-danger">*</span></label>
                        <select name="id_pemilik" id="id_pemilik" class="form-select" required>
                            <option value="">-- Pilih Pemilik Terdaftar --</option>
                            <?php while ($p = mysqli_fetch_assoc($resPemilik)): ?>
                                <option value="<?= $p['id_pemilik'] ?>" <?= $id_pemilik === (int)$p['id_pemilik'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['nama_pemilik']) ?> (No HP: <?= htmlspecialchars($p['no_hp']) ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <small class="text-muted">Jika belum terdaftar, silakan buat data pemilik baru terlebih dahulu.</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nama_hewan" class="form-label fw-semibold">Nama Panggilan Hewan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_hewan" name="nama_hewan" value="<?= htmlspecialchars($nama_hewan) ?>" placeholder="Contoh: Milo / Bobby" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="jenis_hewan" class="form-label fw-semibold">Jenis Hewan <span class="text-danger">*</span></label>
                            <select name="jenis_hewan" id="jenis_hewan" class="form-select" required>
                                <?php foreach (['Kucing', 'Anjing', 'Kelinci', 'Hamster', 'Burung', 'Reptil'] as $j): ?>
                                    <option value="<?= $j ?>" <?= $jenis_hewan === $j ? 'selected' : '' ?>><?= $j ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="ras" class="form-label fw-semibold">Ras / Breed <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="ras" name="ras" value="<?= htmlspecialchars($ras) ?>" placeholder="Contoh: Persia Peaknose / Golden Retriever" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="jenis_kelamin" class="form-label fw-semibold">Jenis Kelamin <span class="text-danger">*</span></label>
                            <select name="jenis_kelamin" id="jenis_kelamin" class="form-select" required>
                                <option value="Jantan" <?= $jenis_kelamin === 'Jantan' ? 'selected' : '' ?>>Jantan</option>
                                <option value="Betina" <?= $jenis_kelamin === 'Betina' ? 'selected' : '' ?>>Betina</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="umur_bulan" class="form-label fw-semibold">Umur (Bulan) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="umur_bulan" name="umur_bulan" min="0" value="<?= htmlspecialchars($umur_bulan) ?>" placeholder="Contoh: 12 (1 tahun)" required>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="berat_kg" class="form-label fw-semibold">Berat Badan (Kg) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="berat_kg" name="berat_kg" min="0.01" value="<?= htmlspecialchars($berat_kg) ?>" placeholder="Contoh: 3.50" required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= base_url('hewan/index.php') ?>" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Simpan Data Hewan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

