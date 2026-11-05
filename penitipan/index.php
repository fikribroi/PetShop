<?php
$pageTitle = 'Transaksi Penitipan Hewan';
$pageSubtitle = 'Tabel 5: Transaksi utama penitipan (Multi-Table JOIN: Hewan, Pemilik, Kandang, Layanan)';
require_once __DIR__ . '/../includes/header.php';

// Filter & Parameter
$search = cleanInput($_GET['search'] ?? '');
$status = cleanInput($_GET['status'] ?? '');
$sort   = cleanInput($_GET['sort'] ?? 'p.id_penitipan');
$order  = cleanInput($_GET['order'] ?? 'DESC');

$allowedSort = [
    'p.id_penitipan'    => 'p.id_penitipan',
    'p.kode_transaksi'  => 'p.kode_transaksi',
    'p.tgl_masuk'       => 'p.tgl_masuk',
    'p.total_biaya'     => 'p.total_biaya',
    'p.status_penitipan'=> 'p.status_penitipan',
    'h.nama_hewan'      => 'h.nama_hewan',
    'pm.nama_pemilik'   => 'pm.nama_pemilik'
];
if (!array_key_exists($sort, $allowedSort)) {
    $sort = 'p.id_penitipan';
}
$order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

// Query JOIN 5 TABEL LENGKAP (Implementasi Soal Poin 4)
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
    pm.id_pemilik,
    pm.nama_pemilik,
    pm.no_hp,
    k.id_kandang,
    k.nomor_kandang,
    k.tipe_kandang,
    k.tarif_per_hari,
    l.id_layanan,
    l.nama_layanan,
    l.tarif_layanan
FROM penitipan p
INNER JOIN hewan h ON p.id_hewan = h.id_hewan
INNER JOIN pemilik pm ON h.id_pemilik = pm.id_pemilik
INNER JOIN kandang k ON p.id_kandang = k.id_kandang
INNER JOIN layanan l ON p.id_layanan = l.id_layanan
WHERE 1=1";

if (!empty($search)) {
    $searchEscaped = mysqli_real_escape_string($conn, $search);
    $sql .= " AND (p.kode_transaksi LIKE '%$searchEscaped%' 
                 OR h.nama_hewan LIKE '%$searchEscaped%' 
                 OR pm.nama_pemilik LIKE '%$searchEscaped%' 
                 OR k.nomor_kandang LIKE '%$searchEscaped%' 
                 OR l.nama_layanan LIKE '%$searchEscaped%')";
}

if (!empty($status)) {
    $statusEscaped = mysqli_real_escape_string($conn, $status);
    $sql .= " AND p.status_penitipan = '$statusEscaped'";
}

$sql .= " ORDER BY $sort $order";
$result = mysqli_query($conn, $sql);
$totalRows = mysqli_num_rows($result);
?>

<!-- FILTER & SEARCH BAR -->
<div class="filter-bar">
    <form action="" method="GET" class="row g-2 align-items-center">
        <div class="col-md-4">
            <div class="input-group">
                <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Cari kode, hewan, pemilik, kandang..." value="<?= htmlspecialchars($search) ?>">
            </div>
        </div>

        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">Semua Status</option>
                <option value="Booking" <?= $status === 'Booking' ? 'selected' : '' ?>>Booking</option>
                <option value="Aktif" <?= $status === 'Aktif' ? 'selected' : '' ?>>Aktif (Menginap)</option>
                <option value="Selesai" <?= $status === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                <option value="Batal" <?= $status === 'Batal' ? 'selected' : '' ?>>Batal</option>
            </select>
        </div>

        <div class="col-md-3">
            <select name="sort" class="form-select">
                <option value="p.id_penitipan" <?= $sort === 'p.id_penitipan' ? 'selected' : '' ?>>Urutkan: ID Penitipan</option>
                <option value="p.kode_transaksi" <?= $sort === 'p.kode_transaksi' ? 'selected' : '' ?>>Urutkan: Kode Transaksi</option>
                <option value="p.tgl_masuk" <?= $sort === 'p.tgl_masuk' ? 'selected' : '' ?>>Urutkan: Tanggal Masuk</option>
                <option value="p.total_biaya" <?= $sort === 'p.total_biaya' ? 'selected' : '' ?>>Urutkan: Total Biaya</option>
                <option value="pm.nama_pemilik" <?= $sort === 'pm.nama_pemilik' ? 'selected' : '' ?>>Urutkan: Nama Pemilik</option>
                <option value="h.nama_hewan" <?= $sort === 'h.nama_hewan' ? 'selected' : '' ?>>Urutkan: Nama Hewan</option>
            </select>
        </div>

        <div class="col-md-1">
            <select name="order" class="form-select">
                <option value="DESC" <?= $order === 'DESC' ? 'selected' : '' ?>>Z-A (Terbaru)</option>
                <option value="ASC" <?= $order === 'ASC' ? 'selected' : '' ?>>A-Z (Terlama)</option>
            </select>
        </div>

        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-funnel me-1"></i> Filter
            </button>
            <?php if (!empty($search) || !empty($status) || $sort !== 'p.id_penitipan' || $order !== 'DESC'): ?>
                <a href="<?= base_url('penitipan/index.php') ?>" class="btn btn-outline-secondary" title="Reset Filter">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- KONTEN TABEL PENITIPAN -->
<div class="card card-custom">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <span class="fw-bold"><i class="bi bi-calendar2-heart-fill me-2 text-primary"></i>Daftar Transaksi Penitipan Hewan</span>
            <span class="badge bg-secondary-subtle text-secondary ms-2"><?= $totalRows ?> Transaksi</span>
        </div>
        <a href="<?= base_url('penitipan/tambah.php') ?>" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Tambah Transaksi Penitipan
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-custom table-hover mb-0">
            <thead>
                <tr>
                    <th>Kode TRX</th>
                    <th>Pemilik & Kontak</th>
                    <th>Hewan Peliharaan</th>
                    <th>Kandang & Layanan</th>
                    <th>Periode & Durasi</th>
                    <th>Total Biaya</th>
                    <th>Status</th>
                    <th class="text-center" style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $totalRows > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td>
                                <span class="badge bg-light text-dark font-monospace border fw-bold">
                                    <?= htmlspecialchars($row['kode_transaksi']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($row['nama_pemilik']) ?></div>
                                <small class="text-muted"><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($row['no_hp']) ?></small>
                            </td>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($row['nama_hewan']) ?></div>
                                <span class="badge badge-pet-<?= strtolower($row['jenis_hewan']) ?>">
                                    <?= htmlspecialchars($row['jenis_hewan']) ?> (<?= htmlspecialchars($row['ras']) ?>)
                                </span>
                            </td>
                            <td>
                                <div>
                                    <span class="badge bg-secondary me-1"><?= htmlspecialchars($row['nomor_kandang']) ?></span>
                                    <small class="text-muted"><?= htmlspecialchars($row['tipe_kandang']) ?></small>
                                </div>
                                <div class="small text-truncate mt-1" style="max-width: 180px;" title="<?= htmlspecialchars($row['nama_layanan']) ?>">
                                    <i class="bi bi-check2-circle text-success me-1"></i><?= htmlspecialchars($row['nama_layanan']) ?>
                                </div>
                            </td>
                            <td>
                                <div class="small"><i class="bi bi-calendar-event text-success me-1"></i> <?= date('d/m/Y', strtotime($row['tgl_masuk'])) ?></div>
                                <div class="small"><i class="bi bi-calendar-check text-danger me-1"></i> <?= date('d/m/Y', strtotime($row['tgl_keluar'])) ?></div>
                                <span class="badge bg-light text-secondary border mt-1"><?= $row['total_hari'] ?> Hari</span>
                            </td>
                            <td>
                                <span class="fw-bold text-success fs-6"><?= formatRupiah($row['total_biaya']) ?></span>
                            </td>
                            <td>
                                <span class="badge badge-status-<?= strtolower($row['status_penitipan']) ?>">
                                    <?= htmlspecialchars($row['status_penitipan']) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= base_url('penitipan/detail.php?id=' . $row['id_penitipan']) ?>" class="btn btn-outline-info" title="Lihat & Cetak Invoice">
                                        <i class="bi bi-receipt"></i>
                                    </a>
                                    <a href="<?= base_url('penitipan/edit.php?id=' . $row['id_penitipan']) ?>" class="btn btn-outline-warning" title="Edit Transaksi">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="<?= base_url('penitipan/hapus.php?id=' . $row['id_penitipan']) ?>" 
                                       class="btn btn-outline-danger btn-delete-confirm" 
                                       data-name="Transaksi <?= htmlspecialchars($row['kode_transaksi']) ?>"
                                       title="Hapus Transaksi">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            Tidak ada data transaksi penitipan yang sesuai kriteria pencarian.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

