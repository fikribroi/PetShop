<?php
$pageTitle = 'Data Kandang & Ruangan';
$pageSubtitle = 'Tabel 3: Fasilitas kamar & ruang penitipan hewan';
require_once __DIR__ . '/../includes/header.php';

// Filter & Parameter
$search = cleanInput($_GET['search'] ?? '');
$tipe   = cleanInput($_GET['tipe'] ?? '');
$status = cleanInput($_GET['status'] ?? '');
$sort   = cleanInput($_GET['sort'] ?? 'id_kandang');
$order  = cleanInput($_GET['order'] ?? 'ASC');

$allowedSort = ['id_kandang', 'nomor_kandang', 'tipe_kandang', 'tarif_per_hari', 'status_kandang'];
if (!in_array($sort, $allowedSort)) {
    $sort = 'id_kandang';
}
$order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

// Query data kandang
$sql = "SELECT * FROM kandang WHERE 1=1";

if (!empty($search)) {
    $searchEscaped = mysqli_real_escape_string($conn, $search);
    $sql .= " AND nomor_kandang LIKE '%$searchEscaped%'";
}

if (!empty($tipe)) {
    $tipeEscaped = mysqli_real_escape_string($conn, $tipe);
    $sql .= " AND tipe_kandang = '$tipeEscaped'";
}

if (!empty($status)) {
    $statusEscaped = mysqli_real_escape_string($conn, $status);
    $sql .= " AND status_kandang = '$statusEscaped'";
}

$sql .= " ORDER BY $sort $order";
$result = mysqli_query($conn, $sql);
$totalRows = mysqli_num_rows($result);
?>

<!-- FILTER & SEARCH BAR -->
<div class="filter-bar">
    <form action="" method="GET" class="row g-2 align-items-center">
        <div class="col-md-3">
            <div class="input-group">
                <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Cari nomor kandang (K-01)..." value="<?= htmlspecialchars($search) ?>">
            </div>
        </div>

        <div class="col-md-2">
            <select name="tipe" class="form-select">
                <option value="">Semua Tipe Kandang</option>
                <option value="Standar" <?= $tipe === 'Standar' ? 'selected' : '' ?>>Standar</option>
                <option value="VIP" <?= $tipe === 'VIP' ? 'selected' : '' ?>>VIP</option>
                <option value="VVIP" <?= $tipe === 'VVIP' ? 'selected' : '' ?>>VVIP</option>
            </select>
        </div>

        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">Semua Status</option>
                <option value="Tersedia" <?= $status === 'Tersedia' ? 'selected' : '' ?>>Tersedia</option>
                <option value="Terisi" <?= $status === 'Terisi' ? 'selected' : '' ?>>Terisi</option>
                <option value="Perbaikan" <?= $status === 'Perbaikan' ? 'selected' : '' ?>>Perbaikan</option>
            </select>
        </div>

        <div class="col-md-2">
            <select name="sort" class="form-select">
                <option value="id_kandang" <?= $sort === 'id_kandang' ? 'selected' : '' ?>>ID Kandang</option>
                <option value="nomor_kandang" <?= $sort === 'nomor_kandang' ? 'selected' : '' ?>>Nomor Kandang</option>
                <option value="tarif_per_hari" <?= $sort === 'tarif_per_hari' ? 'selected' : '' ?>>Tarif / Hari</option>
                <option value="status_kandang" <?= $sort === 'status_kandang' ? 'selected' : '' ?>>Status</option>
            </select>
        </div>

        <div class="col-md-1">
            <select name="order" class="form-select">
                <option value="ASC" <?= $order === 'ASC' ? 'selected' : '' ?>>A-Z</option>
                <option value="DESC" <?= $order === 'DESC' ? 'selected' : '' ?>>Z-A</option>
            </select>
        </div>

        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-funnel me-1"></i> Filter
            </button>
            <?php if (!empty($search) || !empty($tipe) || !empty($status) || $sort !== 'id_kandang' || $order !== 'ASC'): ?>
                <a href="<?= base_url('kandang/index.php') ?>" class="btn btn-outline-secondary" title="Reset">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- KONTEN TABEL -->
<div class="card card-custom">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <span class="fw-bold"><i class="bi bi-box-seam-fill me-2 text-primary"></i>Daftar Kandang & Fasilitas</span>
            <span class="badge bg-secondary-subtle text-secondary ms-2"><?= $totalRows ?> Data Ditemukan</span>
        </div>
        <a href="<?= base_url('kandang/tambah.php') ?>" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Tambah Kandang Baru
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-custom table-hover mb-0">
            <thead>
                <tr>
                    <th style="width: 60px;">ID</th>
                    <th>Nomor Kandang</th>
                    <th>Tipe Ruangan</th>
                    <th>Kapasitas</th>
                    <th>Tarif per Hari</th>
                    <th>Status</th>
                    <th class="text-center" style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $totalRows > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><span class="badge bg-light text-secondary border">#<?= $row['id_kandang'] ?></span></td>
                            <td>
                                <strong class="fs-6 font-monospace text-dark"><?= htmlspecialchars($row['nomor_kandang']) ?></strong>
                            </td>
                            <td>
                                <?php if ($row['tipe_kandang'] === 'VVIP'): ?>
                                    <span class="badge bg-warning text-dark"><i class="bi bi-star-fill me-1"></i> VVIP Suite</span>
                                <?php elseif ($row['tipe_kandang'] === 'VIP'): ?>
                                    <span class="badge bg-primary"><i class="bi bi-award-fill me-1"></i> VIP Deluxe</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Standar</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $row['kapasitas'] ?> Ekor</td>
                            <td class="fw-bold text-success"><?= formatRupiah($row['tarif_per_hari']) ?></td>
                            <td>
                                <span class="badge badge-status-<?= strtolower($row['status_kandang']) ?>">
                                    <?php if ($row['status_kandang'] === 'Tersedia'): ?>
                                        <i class="bi bi-check-circle me-1"></i>
                                    <?php elseif ($row['status_kandang'] === 'Terisi'): ?>
                                        <i class="bi bi-lock-fill me-1"></i>
                                    <?php else: ?>
                                        <i class="bi bi-tools me-1"></i>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($row['status_kandang']) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= base_url('kandang/edit.php?id=' . $row['id_kandang']) ?>" class="btn btn-outline-warning" title="Edit Data">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="<?= base_url('kandang/hapus.php?id=' . $row['id_kandang']) ?>" 
                                       class="btn btn-outline-danger btn-delete-confirm" 
                                       data-name="Kandang <?= htmlspecialchars($row['nomor_kandang']) ?>"
                                       title="Hapus Data">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            Tidak ada data kandang yang sesuai kriteria pencarian.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

