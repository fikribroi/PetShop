<?php
$pageTitle = 'Tambah Transaksi Penitipan';
$pageSubtitle = 'Input transaksi penitipan hewan baru dengan relasi Foreign Key';
require_once __DIR__ . '/../includes/header.php';

// Ambil data untuk dropdown Foreign Key
// 1. Data Hewan beserta nama Pemiliknya (JOIN)
$sqlHewan = "SELECT h.id_hewan, h.nama_hewan, h.jenis_hewan, h.ras, pm.nama_pemilik 
             FROM hewan h 
             INNER JOIN pemilik pm ON h.id_pemilik = pm.id_pemilik 
             ORDER BY h.nama_hewan ASC";
$resHewan = mysqli_query($conn, $sqlHewan);

// 2. Data Kandang
$sqlKandang = "SELECT id_kandang, nomor_kandang, tipe_kandang, tarif_per_hari, status_kandang 
               FROM kandang 
               ORDER BY nomor_kandang ASC";
$resKandang = mysqli_query($conn, $sqlKandang);

// 3. Data Layanan
$sqlLayanan = "SELECT id_layanan, nama_layanan, kategori, tarif_layanan 
               FROM layanan 
               ORDER BY kategori ASC, tarif_layanan ASC";
$resLayanan = mysqli_query($conn, $sqlLayanan);

$errors = [];
$kode_transaksi   = generateKodeTransaksi($conn);
$id_hewan         = (int)($_POST['id_hewan'] ?? 0);
$id_kandang       = (int)($_POST['id_kandang'] ?? 0);
$id_layanan       = (int)($_POST['id_layanan'] ?? 0);
$tgl_masuk        = cleanInput($_POST['tgl_masuk'] ?? date('Y-m-d'));
$tgl_keluar       = cleanInput($_POST['tgl_keluar'] ?? date('Y-m-d', strtotime('+3 days')));
$status_penitipan = cleanInput($_POST['status_penitipan'] ?? 'Aktif');
$catatan          = cleanInput($_POST['catatan'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_transaksi = cleanInput($_POST['kode_transaksi'] ?? $kode_transaksi);

    if ($id_hewan <= 0) $errors[] = "Silakan pilih hewan peliharaan!";
    if ($id_kandang <= 0) $errors[] = "Silakan pilih kandang!";
    if ($id_layanan <= 0) $errors[] = "Silakan pilih layanan!";
    if (empty($tgl_masuk) || empty($tgl_keluar)) $errors[] = "Tanggal masuk dan keluar wajib diisi!";

    // Validasi tanggal
    $timeMasuk = strtotime($tgl_masuk);
    $timeKeluar = strtotime($tgl_keluar);
    if ($timeKeluar < $timeMasuk) {
        $errors[] = "Tanggal keluar tidak boleh lebih awal dari tanggal masuk!";
    }

    // Hitung durasi hari
    $datediff = $timeKeluar - $timeMasuk;
    $total_hari = round($datediff / (60 * 60 * 24));
    if ($total_hari <= 0) $total_hari = 1;

    // Ambil tarif kandang dan layanan langsung dari database untuk keamanan integritas data
    $qKandang = mysqli_query($conn, "SELECT tarif_per_hari FROM kandang WHERE id_kandang = $id_kandang LIMIT 1");
    $dataKandang = mysqli_fetch_assoc($qKandang);
    $tarif_kandang = (float)($dataKandang['tarif_per_hari'] ?? 0);

    $qLayanan = mysqli_query($conn, "SELECT tarif_layanan FROM layanan WHERE id_layanan = $id_layanan LIMIT 1");
    $dataLayanan = mysqli_fetch_assoc($qLayanan);
    $tarif_layanan = (float)($dataLayanan['tarif_layanan'] ?? 0);

    // Hitung total biaya
    $total_biaya = ($total_hari * $tarif_kandang) + $tarif_layanan;

    if (empty($errors)) {
        $sql = "INSERT INTO penitipan (kode_transaksi, id_hewan, id_kandang, id_layanan, tgl_masuk, tgl_keluar, total_hari, total_biaya, status_penitipan, catatan) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "siiissidss", $kode_transaksi, $id_hewan, $id_kandang, $id_layanan, $tgl_masuk, $tgl_keluar, $total_hari, $total_biaya, $status_penitipan, $catatan);

        if (mysqli_stmt_execute($stmt)) {
            $insertedId = mysqli_insert_id($conn);

            // Update status kandang menjadi 'Terisi' jika transaksi aktif
            if ($status_penitipan === 'Aktif') {
                mysqli_query($conn, "UPDATE kandang SET status_kandang = 'Terisi' WHERE id_kandang = $id_kandang");
            }

            setFlashMessage('success', "Transaksi penitipan dengan kode '$kode_transaksi' berhasil disimpan!");
            header("Location: " . base_url('penitipan/detail.php?id=' . $insertedId));
            exit;
        } else {
            $errors[] = "Gagal menyimpan transaksi: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card card-custom">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-calendar2-plus me-2 text-primary"></i>Form Transaksi Penitipan Baru</span>
                <a href="<?= base_url('penitipan/index.php') ?>" class="btn btn-sm btn-outline-secondary">
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

                <form action="" method="POST" id="formPenitipan">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="kode_transaksi" class="form-label fw-semibold">Kode Transaksi (Otomatis)</label>
                            <input type="text" class="form-control font-monospace fw-bold bg-light" id="kode_transaksi" name="kode_transaksi" value="<?= htmlspecialchars($kode_transaksi) ?>" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status_penitipan" class="form-label fw-semibold">Status Penitipan <span class="text-danger">*</span></label>
                            <select name="status_penitipan" id="status_penitipan" class="form-select" required>
                                <option value="Aktif" <?= $status_penitipan === 'Aktif' ? 'selected' : '' ?>>Aktif (Hewan Masuk Menginap)</option>
                                <option value="Booking" <?= $status_penitipan === 'Booking' ? 'selected' : '' ?>>Booking (Jadwal Mendatang)</option>
                                <option value="Selesai" <?= $status_penitipan === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                                <option value="Batal" <?= $status_penitipan === 'Batal' ? 'selected' : '' ?>>Batal</option>
                            </select>
                        </div>
                    </div>

                    <!-- Dropdown Foreign Key Hewan -->
                    <div class="mb-3">
                        <label for="id_hewan" class="form-label fw-semibold">Pilih Hewan Peliharaan (Foreign Key Hewan) <span class="text-danger">*</span></label>
                        <select name="id_hewan" id="id_hewan" class="form-select" required>
                            <option value="">-- Pilih Hewan yang akan dititipkan --</option>
                            <?php while ($h = mysqli_fetch_assoc($resHewan)): ?>
                                <option value="<?= $h['id_hewan'] ?>" <?= $id_hewan === (int)$h['id_hewan'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($h['nama_hewan']) ?> (<?= htmlspecialchars($h['jenis_hewan']) ?> - <?= htmlspecialchars($h['ras']) ?>) — Pemilik: <?= htmlspecialchars($h['nama_pemilik']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="row">
                        <!-- Dropdown Foreign Key Kandang -->
                        <div class="col-md-6 mb-3">
                            <label for="id_kandang" class="form-label fw-semibold">Pilih Kandang (Foreign Key Kandang) <span class="text-danger">*</span></label>
                            <select name="id_kandang" id="id_kandang" class="form-select" required>
                                <option value="" data-tarif="0">-- Pilih Ruangan / Kandang --</option>
                                <?php while ($k = mysqli_fetch_assoc($resKandang)): ?>
                                    <option value="<?= $k['id_kandang'] ?>" 
                                            data-tarif="<?= $k['tarif_per_hari'] ?>" 
                                            <?= $id_kandang === (int)$k['id_kandang'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($k['nomor_kandang']) ?> (<?= htmlspecialchars($k['tipe_kandang']) ?>) - <?= formatRupiah($k['tarif_per_hari']) ?>/hari [<?= $k['status_kandang'] ?>]
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- Dropdown Foreign Key Layanan -->
                        <div class="col-md-6 mb-3">
                            <label for="id_layanan" class="form-label fw-semibold">Pilih Layanan / Paket (Foreign Key Layanan) <span class="text-danger">*</span></label>
                            <select name="id_layanan" id="id_layanan" class="form-select" required>
                                <option value="" data-tarif="0">-- Pilih Paket Layanan --</option>
                                <?php while ($l = mysqli_fetch_assoc($resLayanan)): ?>
                                    <option value="<?= $l['id_layanan'] ?>" 
                                            data-tarif="<?= $l['tarif_layanan'] ?>" 
                                            <?= $id_layanan === (int)$l['id_layanan'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($l['nama_layanan']) ?> (<?= htmlspecialchars($l['kategori']) ?>) - <?= formatRupiah($l['tarif_layanan']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="tgl_masuk" class="form-label fw-semibold">Tanggal Masuk (Check-In) <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tgl_masuk" name="tgl_masuk" value="<?= htmlspecialchars($tgl_masuk) ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="tgl_keluar" class="form-label fw-semibold">Tanggal Keluar (Check-Out) <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tgl_keluar" name="tgl_keluar" value="<?= htmlspecialchars($tgl_keluar) ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="total_hari" class="form-label fw-semibold">Estimasi Durasi (Hari)</label>
                            <input type="number" class="form-control bg-light" id="total_hari" name="total_hari" value="3" readonly>
                        </div>
                    </div>

                    <!-- Kalkulasi Live Card -->
                    <div class="card bg-primary-subtle border-0 rounded-3 p-3 mb-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted small fw-semibold">Perhitungan Biaya Penitipan Otomatis:</div>
                                <small class="text-muted">(Durasi Hari × Tarif Kandang) + Tarif Layanan</small>
                            </div>
                            <div class="text-end">
                                <div class="fs-4 fw-bold text-teal" style="color: #0d9488;" id="total_biaya_preview">Rp 0</div>
                                <input type="hidden" name="total_biaya" id="total_biaya" value="0">
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="catatan" class="form-label fw-semibold">Catatan Khusus Penitipan</label>
                        <textarea class="form-control" id="catatan" name="catatan" rows="3" placeholder="Contoh: Jadwal makan, alergi hewan, kebiasaan, nomor kontak darurat keluarga..."><?= htmlspecialchars($catatan) ?></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= base_url('penitipan/index.php') ?>" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Simpan Transaksi Penitipan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Panggil hitungTotal saat halaman pertama kali termuat
document.addEventListener('DOMContentLoaded', function() {
    const tglMasuk = document.getElementById('tgl_masuk');
    if (tglMasuk) {
        tglMasuk.dispatchEvent(new Event('change'));
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

