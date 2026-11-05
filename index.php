<?php
$pageTitle = 'Dashboard';
$pageSubtitle = 'Ringkasan Sistem Informasi Pet Shop & Penitipan Hewan';
require_once __DIR__ . '/includes/header.php';

// Ambil statistik data dari database
// 1. Total Pemilik
$resPemilik = mysqli_query($conn, "SELECT COUNT(*) as total FROM pemilik");
$totalPemilik = mysqli_fetch_assoc($resPemilik)['total'] ?? 0;

// 2. Total Hewan
$resHewan = mysqli_query($conn, "SELECT COUNT(*) as total FROM hewan");
$totalHewan = mysqli_fetch_assoc($resHewan)['total'] ?? 0;

// 3. Kandang Tersedia vs Total
$resKandang = mysqli_query($conn, "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status_kandang = 'Tersedia' THEN 1 ELSE 0 END) as tersedia,
    SUM(CASE WHEN status_kandang = 'Terisi' THEN 1 ELSE 0 END) as terisi
FROM kandang");
$statKandang = mysqli_fetch_assoc($resKandang);

// 4. Penitipan Aktif & Booking
$resPenitipan = mysqli_query($conn, "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status_penitipan = 'Aktif' THEN 1 ELSE 0 END) as aktif,
    SUM(CASE WHEN status_penitipan = 'Booking' THEN 1 ELSE 0 END) as booking,
    SUM(CASE WHEN status_penitipan = 'Selesai' THEN total_biaya ELSE 0 END) as total_pendapatan
FROM penitipan");
$statPenitipan = mysqli_fetch_assoc($resPenitipan);

// 5. Data Penitipan Terbaru dengan JOIN 5 TABEL (Sesuai Soal No 4)
$queryTerbaru = "SELECT 
    p.id_penitipan,
    p.kode_transaksi,
    pm.nama_pemilik,
    h.nama_hewan,
    h.jenis_hewan,
    k.nomor_kandang,
    k.tipe_kandang,
    l.nama_layanan,
    p.tgl_masuk,
    p.tgl_keluar,
    p.total_biaya,
    p.status_penitipan
FROM penitipan p
INNER JOIN hewan h ON p.id_hewan = h.id_hewan
INNER JOIN pemilik pm ON h.id_pemilik = pm.id_pemilik
INNER JOIN kandang k ON p.id_kandang = k.id_kandang
INNER JOIN layanan l ON p.id_layanan = l.id_layanan
ORDER BY p.id_penitipan DESC
LIMIT 5";
$resTerbaru = mysqli_query($conn, $queryTerbaru);
?>

<!-- KARTU STATISTIK -->
<div class="row g-3 mb-4">
    <!-- Pemilik -->
    <div class="col-sm-6 col-xl-3">
        <div class="card-stat d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small fw-semibold">Total Pemilik</span>
                <h3 class="fw-bold mb-0 mt-1"><?= number_format($totalPemilik) ?></h3>
                <small class="text-success"><i class="bi bi-people me-1"></i> Pelanggan Aktif</small>
            </div>
            <div class="stat-icon bg-primary-subtle text-primary">
                <i class="bi bi-people-fill"></i>
            </div>
        </div>
    </div>

    <!-- Hewan -->
    <div class="col-sm-6 col-xl-3">
        <div class="card-stat d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small fw-semibold">Hewan Terdaftar</span>
                <h3 class="fw-bold mb-0 mt-1"><?= number_format($totalHewan) ?></h3>
                <small class="text-info"><i class="bi bi-heart me-1"></i> Kucing, Anjing, dll</small>
            </div>
            <div class="stat-icon bg-info-subtle text-info">
                <i class="bi bi-github"></i>
            </div>
        </div>
    </div>

    <!-- Kandang -->
    <div class="col-sm-6 col-xl-3">
        <div class="card-stat d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small fw-semibold">Kandang Tersedia</span>
                <h3 class="fw-bold mb-0 mt-1"><?= number_format($statKandang['tersedia'] ?? 0) ?> <span class="fs-6 fw-normal text-muted">/ <?= $statKandang['total'] ?? 0 ?></span></h3>
                <small class="text-warning"><i class="bi bi-door-open me-1"></i> <?= $statKandang['terisi'] ?? 0 ?> Sedang Terisi</small>
            </div>
            <div class="stat-icon bg-warning-subtle text-warning">
                <i class="bi bi-box-seam-fill"></i>
            </div>
        </div>
    </div>

    <!-- Pendapatan -->
    <div class="col-sm-6 col-xl-3">
        <div class="card-stat d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small fw-semibold">Pendapatan Selesai</span>
                <h4 class="fw-bold mb-0 mt-1 text-success"><?= formatRupiah($statPenitipan['total_pendapatan'] ?? 0) ?></h4>
                <small class="text-muted"><i class="bi bi-check2-circle me-1"></i> <?= $statPenitipan['aktif'] ?? 0 ?> Aktif | <?= $statPenitipan['booking'] ?? 0 ?> Booking</small>
            </div>
            <div class="stat-icon bg-success-subtle text-success">
                <i class="bi bi-cash-stack"></i>
            </div>
        </div>
    </div>
</div>

<!-- INFO BANNER TUBES SBD -->
<div class="alert alert-info border-0 shadow-sm d-flex flex-wrap align-items-center justify-content-between mb-4 p-3 rounded-3" style="background-color: #f0fdfa; border-left: 5px solid #0d9488 !important;">
    <div class="d-flex align-items-center me-3 mb-2 mb-md-0">
        <i class="bi bi-info-circle-fill fs-3 text-teal me-3" style="color: #0d9488;"></i>
        <div>
            <h6 class="mb-1 fw-bold text-dark">Informasi Tugas Besar Sistem Basis Data (SBD)</h6>
            <span class="small text-muted">Sistem ini memenuhi seluruh kriteria: <strong>5 Tabel Berelasi</strong> (Pemilik, Hewan, Kandang, Layanan, Penitipan), <strong>Multi-Table JOIN</strong>, <strong>Halaman CRUD Terpisah</strong>, Pencarian, Filter & Sortir.</span>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('penitipan/tambah.php') ?>" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Input Penitipan
        </a>
        <a href="<?= base_url('database.sql') ?>" download class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-download me-1"></i> Unduh SQL
        </a>
    </div>
</div>

<!-- TABEL TRANSAKSI PENITIPAN TERBARU (JOIN) -->
<div class="card card-custom mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h6 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>5 Transaksi Penitipan Hewan Terbaru</h6>
            <small class="text-muted">Implementasi JOIN antara tabel <code>penitipan</code>, <code>hewan</code>, <code>pemilik</code>, <code>kandang</code>, dan <code>layanan</code></small>
        </div>
        <a href="<?= base_url('penitipan/index.php') ?>" class="btn btn-sm btn-outline-primary">
            Lihat Semua Transaksi <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-custom table-hover mb-0">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Pemilik</th>
                    <th>Hewan</th>
                    <th>Kandang</th>
                    <th>Layanan</th>
                    <th>Durasi Masuk - Keluar</th>
                    <th>Total Biaya</th>
                    <th>Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($resTerbaru && mysqli_num_rows($resTerbaru) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($resTerbaru)): ?>
                        <tr>
                            <td><span class="badge bg-light text-dark font-monospace border"><?= htmlspecialchars($row['kode_transaksi']) ?></span></td>
                            <td><span class="fw-semibold"><?= htmlspecialchars($row['nama_pemilik']) ?></span></td>
                            <td>
                                <div><strong><?= htmlspecialchars($row['nama_hewan']) ?></strong></div>
                                <span class="badge badge-pet-<?= strtolower($row['jenis_hewan']) ?>"><?= htmlspecialchars($row['jenis_hewan']) ?></span>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?= htmlspecialchars($row['nomor_kandang']) ?> (<?= htmlspecialchars($row['tipe_kandang']) ?>)</span>
                            </td>
                            <td><small><?= htmlspecialchars($row['nama_layanan']) ?></small></td>
                            <td>
                                <div class="small text-muted"><i class="bi bi-box-arrow-in-right text-success"></i> <?= date('d/m/Y', strtotime($row['tgl_masuk'])) ?></div>
                                <div class="small text-muted"><i class="bi bi-box-arrow-right text-danger"></i> <?= date('d/m/Y', strtotime($row['tgl_keluar'])) ?></div>
                            </td>
                            <td class="fw-bold text-success"><?= formatRupiah($row['total_biaya']) ?></td>
                            <td>
                                <span class="badge badge-status-<?= strtolower($row['status_penitipan']) ?>">
                                    <?= htmlspecialchars($row['status_penitipan']) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="<?= base_url('penitipan/detail.php?id=' . $row['id_penitipan']) ?>" class="btn btn-sm btn-light border" title="Lihat Invoice">
                                    <i class="bi bi-receipt"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">Belum ada transaksi penitipan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

