<?php
$pageTitle = 'Data Pemilik Hewan';
$pageSubtitle = 'Tabel 1: Master data pelanggan / pemilik hewan peliharaan';
require_once __DIR__ . '/../includes/header.php';

// Parameter Pencarian, Filter & Sortir
$search = cleanInput($_GET['search'] ?? '');
$sort   = cleanInput($_GET['sort'] ?? 'id_pemilik');
$order  = cleanInput($_GET['order'] ?? 'ASC');

// Validasi kolom sort
$allowedSort = ['id_pemilik', 'nama_pemilik', 'no_hp', 'email', 'total_hewan'];
if (!in_array($sort, $allowedSort)) {
    $sort = 'id_pemilik';
}
$order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

// Query dasar dengan LEFT JOIN ke hewan untuk menghitung jumlah peliharaan tiap pemilik
$sql = "SELECT 
    p.id_pemilik,
    p.nama_pemilik,
    p.no_hp,
    p.email,
    p.alamat,
    p.created_at,
    COUNT(h.id_hewan) AS total_hewan
FROM pemilik p
LEFT JOIN hewan h ON p.id_pemilik = h.id_pemilik";

if (!empty($search)) {
    $searchEscaped = mysqli_real_escape_string($conn, $search);
    $sql .= " WHERE (p.nama_pemilik LIKE '%$searchEscaped%' 
                 OR p.no_hp LIKE '%$searchEscaped%' 
                 OR p.email LIKE '%$searchEscaped%' 
                 OR p.alamat LIKE '%$searchEscaped%')";
}

$sql .= " GROUP BY p.id_pemilik ORDER BY $sort $order";
$result = mysqli_query($conn, $sql);
$totalRows = mysqli_num_rows($result);
?>

<!-- FILTER & SEARCH BAR -->
<div class="filter-bar">
    <form action="" method="GET" class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Cari nama, no hp, email, atau alamat..." value="<?= htmlspecialchars($search) ?>">
            </div>
        </div>

        <div class="col-md-3">
            <select name="sort" class="form-select">
                <option value="id_pemilik" <?= $sort === 'id_pemilik' ? 'selected' : '' ?>>Urutkan: ID Pemilik</option>
                <option value="nama_pemilik" <?= $sort === 'nama_pemilik' ? 'selected' : '' ?>>Urutkan: Nama Pemilik</option>
                <option value="total_hewan" <?= $sort === 'total_hewan' ? 'selected' : '' ?>>Urutkan: Jumlah Hewan</option>
            </select>
        </div>

        <div class="col-md-2">
            <select name="order" class="form-select">
                <option value="ASC" <?= $order === 'ASC' ? 'selected' : '' ?>>A-Z (Ascending)</option>
                <option value="DESC" <?= $order === 'DESC' ? 'selected' : '' ?>>Z-A (Descending)</option>
            </select>
        </div>

        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-filter me-1"></i> Terapkan
            </button>
            <?php if (!empty($search) || $sort !== 'id_pemilik' || $order !== 'ASC'): ?>
                <a href="<?= base_url('pemilik/index.php') ?>" class="btn btn-outline-secondary" title="Reset Filter">
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
            <span class="fw-bold"><i class="bi bi-table me-2 text-primary"></i>Daftar Data Pemilik</span>
            <span class="badge bg-secondary-subtle text-secondary ms-2"><?= $totalRows ?> Data Ditemukan</span>
        </div>
        <a href="<?= base_url('pemilik/tambah.php') ?>" class="btn btn-sm btn-primary">
            <i class="bi bi-person-plus-fill me-1"></i> Tambah Pemilik Baru
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-custom table-hover mb-0">
            <thead>
                <tr>
                    <th style="width: 60px;">ID</th>
                    <th>Nama Pemilik</th>
                    <th>Kontak (No HP & Email)</th>
                    <th>Alamat</th>
                    <th class="text-center">Jml Hewan</th>
                    <th class="text-center" style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $totalRows > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><span class="badge bg-light text-secondary border">#<?= $row['id_pemilik'] ?></span></td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($row['nama_pemilik']) ?></div>
                                <small class="text-muted">Terdaftar: <?= date('d/m/Y', strtotime($row['created_at'])) ?></small>
                            </td>
                            <td>
                                <div><i class="bi bi-telephone text-success me-1"></i> <?= htmlspecialchars($row['no_hp']) ?></div>
                                <small class="text-muted"><i class="bi bi-envelope text-primary me-1"></i> <?= htmlspecialchars($row['email'] ?: '-') ?></small>
                            </td>
                            <td>
                                <span class="text-truncate d-inline-block" style="max-width: 250px;" title="<?= htmlspecialchars($row['alamat']) ?>">
                                    <?= htmlspecialchars($row['alamat']) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill bg-info-subtle text-info border px-3">
                                    <i class="bi bi-github me-1"></i> <?= $row['total_hewan'] ?> Ekor
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= base_url('pemilik/edit.php?id=' . $row['id_pemilik']) ?>" class="btn btn-outline-warning" title="Edit Data">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="<?= base_url('pemilik/hapus.php?id=' . $row['id_pemilik']) ?>" 
                                       class="btn btn-outline-danger btn-delete-confirm" 
                                       data-name="<?= htmlspecialchars($row['nama_pemilik']) ?>"
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
                            Tidak ada data pemilik yang sesuai kriteria pencarian.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

