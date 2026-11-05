<?php
$pageTitle = 'Data Layanan & Treatment';
$pageSubtitle = 'Tabel 4: Daftar paket layanan, grooming, dan perawatan medis pet shop';
require_once __DIR__ . '/../includes/header.php';

// Filter & Parameter
$search   = cleanInput($_GET['search'] ?? '');
$kategori = cleanInput($_GET['kategori'] ?? '');
$sort     = cleanInput($_GET['sort'] ?? 'id_layanan');
$order    = cleanInput($_GET['order'] ?? 'ASC');

$allowedSort = ['id_layanan', 'nama_layanan', 'kategori', 'tarif_layanan'];
if (!in_array($sort, $allowedSort)) {
    $sort = 'id_layanan';
}
$order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

// Query data layanan
$sql = "SELECT * FROM layanan WHERE 1=1";

if (!empty($search)) {
    $searchEscaped = mysqli_real_escape_string($conn, $search);
    $sql .= " AND (nama_layanan LIKE '%$searchEscaped%' OR deskripsi LIKE '%$searchEscaped%')";
}

if (!empty($kategori)) {
    $katEscaped = mysqli_real_escape_string($conn, $kategori);
    $sql .= " AND kategori = '$katEscaped'";
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
                <input type="text" name="search" class="form-control" placeholder="Cari nama layanan / paket..." value="<?= htmlspecialchars($search) ?>">
            </div>
        </div>

        <div class="col-md-3">
            <select name="kategori" class="form-select">
                <option value="">Semua Kategori</option>
                <option value="Penitipan Saja" <?= $kategori === 'Penitipan Saja' ? 'selected' : '' ?>>Penitipan Saja</option>
                <option value="Grooming & Spa" <?= $kategori === 'Grooming & Spa' ? 'selected' : '' ?>>Grooming & Spa</option>
                <option value="Kesehatan & Vaksin" <?= $kategori === 'Kesehatan & Vaksin' ? 'selected' : '' ?>>Kesehatan & Vaksin</option>
                <option value="Perawatan Khusus" <?= $kategori === 'Perawatan Khusus' ? 'selected' : '' ?>>Perawatan Khusus</option>
            </select>
        </div>

        <div class="col-md-2">
            <select name="sort" class="form-select">
                <option value="id_layanan" <?= $sort === 'id_layanan' ? 'selected' : '' ?>>ID Layanan</option>
                <option value="nama_layanan" <?= $sort === 'nama_layanan' ? 'selected' : '' ?>>Nama Layanan</option>
                <option value="kategori" <?= $sort === 'kategori' ? 'selected' : '' ?>>Kategori</option>
                <option value="tarif_layanan" <?= $sort === 'tarif_layanan' ? 'selected' : '' ?>>Tarif</option>
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
            <?php if (!empty($search) || !empty($kategori) || $sort !== 'id_layanan' || $order !== 'ASC'): ?>
                <a href="<?= base_url('layanan/index.php') ?>" class="btn btn-outline-secondary" title="Reset">
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
            <span class="fw-bold"><i class="bi bi-card-checklist me-2 text-primary"></i>Daftar Layanan & Paket</span>
            <span class="badge bg-secondary-subtle text-secondary ms-2"><?= $totalRows ?> Data Ditemukan</span>
        </div>
        <a href="<?= base_url('layanan/tambah.php') ?>" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Tambah Layanan Baru
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-custom table-hover mb-0">
            <thead>
                <tr>
                    <th style="width: 60px;">ID</th>
                    <th>Nama Layanan</th>
                    <th>Kategori</th>
                    <th>Deskripsi</th>
                    <th>Biaya / Tarif</th>
                    <th class="text-center" style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $totalRows > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><span class="badge bg-light text-secondary border">#<?= $row['id_layanan'] ?></span></td>
                            <td>
                                <strong class="text-dark"><?= htmlspecialchars($row['nama_layanan']) ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-info-subtle text-info border">
                                    <?= htmlspecialchars($row['kategori']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="text-muted small"><?= htmlspecialchars($row['deskripsi'] ?: '-') ?></span>
                            </td>
                            <td>
                                <?php if ($row['tarif_layanan'] == 0): ?>
                                    <span class="badge bg-success-subtle text-success">Termasuk / Gratis</span>
                                <?php else: ?>
                                    <span class="fw-bold text-success"><?= formatRupiah($row['tarif_layanan']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= base_url('layanan/edit.php?id=' . $row['id_layanan']) ?>" class="btn btn-outline-warning" title="Edit Data">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="<?= base_url('layanan/hapus.php?id=' . $row['id_layanan']) ?>" 
                                       class="btn btn-outline-danger btn-delete-confirm" 
                                       data-name="<?= htmlspecialchars($row['nama_layanan']) ?>"
                                       title="Hapus Data">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            Tidak ada data layanan yang sesuai kriteria pencarian.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

