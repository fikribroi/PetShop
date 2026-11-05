<?php
$pageTitle = 'Invoice & Detail Penitipan Hewan';
$pageSubtitle = 'Rincian resmi bukti transaksi penitipan hewan (Implementasi 5 Table JOIN)';
require_once __DIR__ . '/../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    setFlashMessage('danger', 'ID Transaksi tidak valid!');
    header("Location: " . base_url('penitipan/index.php'));
    exit;
}

// Query JOIN 5 TABEL LENGKAP
$sql = "SELECT 
    p.id_penitipan,
    p.kode_transaksi,
    p.tgl_masuk,
    p.tgl_keluar,
    p.total_hari,
    p.total_biaya,
    p.status_penitipan,
    p.catatan,
    p.created_at,
    h.id_hewan,
    h.nama_hewan,
    h.jenis_hewan,
    h.ras,
    h.umur_bulan,
    h.jenis_kelamin,
    h.berat_kg,
    pm.id_pemilik,
    pm.nama_pemilik,
    pm.no_hp,
    pm.email,
    pm.alamat,
    k.id_kandang,
    k.nomor_kandang,
    k.tipe_kandang,
    k.tarif_per_hari,
    l.id_layanan,
    l.nama_layanan,
    l.kategori AS kategori_layanan,
    l.tarif_layanan,
    l.deskripsi AS deskripsi_layanan
FROM penitipan p
INNER JOIN hewan h ON p.id_hewan = h.id_hewan
INNER JOIN pemilik pm ON h.id_pemilik = pm.id_pemilik
INNER JOIN kandang k ON p.id_kandang = k.id_kandang
INNER JOIN layanan l ON p.id_layanan = l.id_layanan
WHERE p.id_penitipan = ?
LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$trx = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$trx) {
    setFlashMessage('danger', 'Data transaksi penitipan tidak ditemukan!');
    header("Location: " . base_url('penitipan/index.php'));
    exit;
}

$subtotalKandang = $trx['total_hari'] * $trx['tarif_per_hari'];
?>

<!-- TOMBOL AKSI ATAS -->
<div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <a href="<?= base_url('penitipan/index.php') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Transaksi
    </a>
    <div class="d-flex gap-2">
        <a href="<?= base_url('penitipan/edit.php?id=' . $trx['id_penitipan']) ?>" class="btn btn-outline-warning">
            <i class="bi bi-pencil-square me-1"></i> Edit Transaksi
        </a>
        <button onclick="window.print()" class="btn btn-primary">
            <i class="bi bi-printer me-1"></i> Cetak / Print Invoice
        </button>
    </div>
</div>

<!-- INVOICE CARD -->
<div class="card card-custom p-4 p-md-5">
    <!-- Header Invoice -->
    <div class="d-flex flex-wrap justify-content-between align-items-center pb-4 mb-4 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-shield-heart-fill fs-2" style="color: #0d9488;"></i>
                <h3 class="fw-bold mb-0 text-dark">PetCare Studio</h3>
            </div>
            <p class="text-muted small mb-0">Pet Shop, Grooming & Penitipan Hewan Terpercaya</p>
            <p class="text-muted small mb-0">Jl. Pelindung Hewan No. 88, Kota Bandung | Telp: (022) 7654-3210</p>
        </div>
        <div class="text-md-end mt-3 mt-md-0">
            <span class="badge badge-status-<?= strtolower($trx['status_penitipan']) ?> fs-6 px-3 py-2 mb-2 d-inline-block">
                STATUS: <?= strtoupper($trx['status_penitipan']) ?>
            </span>
            <div class="fs-5 font-monospace fw-bold text-dark"><?= htmlspecialchars($trx['kode_transaksi']) ?></div>
            <small class="text-muted">Dibuat: <?= date('d M Y H:i', strtotime($trx['created_at'])) ?></small>
        </div>
    </div>

    <!-- Informasi 2 Kolom (Pemilik & Hewan) -->
    <div class="row g-4 mb-4">
        <!-- Kolom Pemilik -->
        <div class="col-md-6">
            <div class="p-3 rounded-3 bg-light border h-100">
                <h6 class="fw-bold text-teal mb-3" style="color: #0d9488;">
                    <i class="bi bi-person-badge-fill me-1"></i> Informasi Pelanggan (Pemilik)
                </h6>
                <table class="table table-sm table-borderless mb-0 small">
                    <tr>
                        <td class="text-muted" style="width: 120px;">Nama Lengkap</td>
                        <td>: <strong><?= htmlspecialchars($trx['nama_pemilik']) ?></strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Nomor Kontak</td>
                        <td>: <?= htmlspecialchars($trx['no_hp']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Email</td>
                        <td>: <?= htmlspecialchars($trx['email'] ?: '-') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Alamat</td>
                        <td>: <?= htmlspecialchars($trx['alamat']) ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Kolom Hewan -->
        <div class="col-md-6">
            <div class="p-3 rounded-3 bg-light border h-100">
                <h6 class="fw-bold text-teal mb-3" style="color: #0d9488;">
                    <i class="bi bi-github me-1"></i> Informasi Hewan Peliharaan
                </h6>
                <table class="table table-sm table-borderless mb-0 small">
                    <tr>
                        <td class="text-muted" style="width: 120px;">Nama Hewan</td>
                        <td>: <strong><?= htmlspecialchars($trx['nama_hewan']) ?></strong> (<?= htmlspecialchars($trx['jenis_kelamin']) ?>)</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Jenis & Ras</td>
                        <td>: <?= htmlspecialchars($trx['jenis_hewan']) ?> - <?= htmlspecialchars($trx['ras']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Umur & Berat</td>
                        <td>: <?= $trx['umur_bulan'] ?> Bulan | <?= number_format($trx['berat_kg'], 2) ?> kg</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Catatan Khusus</td>
                        <td>: <?= htmlspecialchars($trx['catatan'] ?: 'Tidak ada instruksi khusus.') ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Rincian Biaya Transaksi -->
    <h6 class="fw-bold mb-3"><i class="bi bi-list-check me-1 text-primary"></i>Rincian Layanan & Fasilitas Penitipan</h6>
    <div class="table-responsive mb-4">
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Deskripsi Fasilitas / Layanan</th>
                    <th class="text-center">Tarif Satuan</th>
                    <th class="text-center">Durasi / Qty</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <!-- Biaya Kandang -->
                <tr>
                    <td>
                        <strong>Sewa Ruang Kandang: <?= htmlspecialchars($trx['nomor_kandang']) ?> (Tipe <?= htmlspecialchars($trx['tipe_kandang']) ?>)</strong>
                        <div class="small text-muted">
                            Check-in: <?= formatTanggalIndo($trx['tgl_masuk']) ?> &bull; Check-out: <?= formatTanggalIndo($trx['tgl_keluar']) ?>
                        </div>
                    </td>
                    <td class="text-center"><?= formatRupiah($trx['tarif_per_hari']) ?> / hari</td>
                    <td class="text-center"><?= $trx['total_hari'] ?> Hari</td>
                    <td class="text-end fw-semibold"><?= formatRupiah($subtotalKandang) ?></td>
                </tr>

                <!-- Biaya Layanan Tambahan -->
                <tr>
                    <td>
                        <strong>Paket Layanan: <?= htmlspecialchars($trx['nama_layanan']) ?></strong>
                        <div class="small text-muted">Kategori: <?= htmlspecialchars($trx['kategori_layanan']) ?> (<?= htmlspecialchars($trx['deskripsi_layanan'] ?: '-') ?>)</div>
                    </td>
                    <td class="text-center"><?= formatRupiah($trx['tarif_layanan']) ?></td>
                    <td class="text-center">1 Paket</td>
                    <td class="text-end fw-semibold"><?= formatRupiah($trx['tarif_layanan']) ?></td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-end fw-bold fs-5">TOTAL BIAYA PENITIPAN</td>
                    <td class="text-end fw-bold fs-5 text-teal" style="color: #0d9488;"><?= formatRupiah($trx['total_biaya']) ?></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Tanda Tangan & Footer Bukti -->
    <div class="row mt-5 pt-3 border-top">
        <div class="col-6 text-center">
            <p class="small text-muted mb-5">Petugas / Admin PetCare,</p>
            <p class="fw-bold mb-0 text-decoration-underline"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Staff PetCare') ?></p>
            <small class="text-muted"><?= htmlspecialchars($_SESSION['role'] ?? 'Staff') ?></small>
        </div>
        <div class="col-6 text-center">
            <p class="small text-muted mb-5">Pemilik Hewan Peliharaan,</p>
            <p class="fw-bold mb-0 text-decoration-underline"><?= htmlspecialchars($trx['nama_pemilik']) ?></p>
            <small class="text-muted">Pelanggan</small>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

