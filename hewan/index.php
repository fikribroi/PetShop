<?php
$pageTitle = 'Data Hewan Peliharaan';
$pageSubtitle = 'Tabel 2: Data hewan peliharaan (Relasi Foreign Key ke Pemilik)';
require_once __DIR__ . '/../includes/header.php';

// Filter & Parameter
$search      = cleanInput($_GET['search'] ?? '');
$jenisHewan  = cleanInput($_GET['jenis_hewan'] ?? '');
$jenisKel    = cleanInput($_GET['jenis_kelamin'] ?? '');
$sort        = cleanInput($_GET['sort'] ?? 'h.id_hewan');
$order       = cleanInput($_GET['order'] ?? 'ASC');

$allowedSort = [
    'h.id_hewan'     => 'h.id_hewan',
    'h.nama_hewan'   => 'h.nama_hewan',
    'p.nama_pemilik' => 'p.nama_pemilik',
    'h.umur_bulan'   => 'h.umur_bulan',
    'h.berat_kg'     => 'h.berat_kg'
];
if (!array_key_exists($sort, $allowedSort)) {
    $sort = 'h.id_hewan';
}
$order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

// Query JOIN antara hewan dan pemilik
$sql = "SELECT 
    h.id_hewan,
    h.nama_hewan,
    h.jenis_hewan,
    h.ras,
    h.umur_bulan,
    h.jenis_kelamin,
    h.berat_kg,
    h.created_at,
    p.id_pemilik,
    p.nama_pemilik,
    p.no_hp
FROM hewan h
INNER JOIN pemilik p ON h.id_pemilik = p.id_pemilik
WHERE 1=1";

if (!empty($search)) {
    $searchEscaped = mysqli_real_escape_string($conn, $search);
    $sql .= " AND (h.nama_hewan LIKE '%$searchEscaped%' 
                 OR h.ras LIKE '%$searchEscaped%' 
                 OR p.nama_pemilik LIKE '%$searchEscaped%')";
}

if (!empty($jenisHewan)) {
    $jenisEscaped = mysqli_real_escape_string($conn, $jenisHewan);
    $sql .= " AND h.jenis_hewan = '$jenisEscaped'";
}

if (!empty($jenisKel)) {
    $kelEscaped = mysqli_real_escape_string($conn, $jenisKel);
    $sql .= " AND h.jenis_kelamin = '$kelEscaped'";
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
                <input type="text" name="search" class="form-control" placeholder="Cari hewan, ras, pemilik..." value="<?= htmlspecialchars($search) ?>">
            </div>
        </div>

        <div class="col-md-2">
            <select name="jenis_hewan" class="form-select">
                <option value="">Semua Jenis Hewan</option>
                <?php foreach (['Kucing', 'Anjing', 'Kelinci', 'Hamster', 'Burung', 'Reptil'] as $j): ?>
                    <option value="<?= $j ?>" <?= $jenisHewan === $j ? 'selected' : '' ?>><?= $j ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-2">
            <select name="jenis_kelamin" class="form-select">
                <option value="">Semua Kelamin</option>
                <option value="Jantan" <?= $jenisKel === 'Jantan' ? 'selected' : '' ?>>Jantan</option>
                <option value="Betina" <?= $jenisKel === 'Betina' ? 'selected' : '' ?>>Betina</option>
            </select>
        </div>

        <div class="col-md-2">
            <select name="sort" class="form-select">
                <option value="h.id_hewan" <?= $sort === 'h.id_hewan' ? 'selected' : '' ?>>ID Hewan</option>
                <option value="h.nama_hewan" <?= $sort === 'h.nama_hewan' ? 'selected' : '' ?>>Nama Hewan</option>
                <option value="p.nama_pemilik" <?= $sort === 'p.nama_pemilik' ? 'selected' : '' ?>>Nama Pemilik</option>
                <option value="h.umur_bulan" <?= $sort === 'h.umur_bulan' ? 'selected' : '' ?>>Umur</option>
                <option value="h.berat_kg" <?= $sort === 'h.berat_kg' ? 'selected' : '' ?>>Berat Badan</option>
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
            <?php if (!empty($search) || !empty($jenisHewan) || !empty($jenisKel) || $sort !== 'h.id_hewan' || $order !== 'ASC'): ?>
                <a href="<?= base_url('hewan/index.php') ?>" class="btn btn-outline-secondary" title="Reset Filter">
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
            <span class="fw-bold"><i class="bi bi-github me-2 text-primary"></i>Daftar Hewan Peliharaan</span>
            <span class="badge bg-secondary-subtle text-secondary ms-2"><?= $totalRows ?> Data Ditemukan</span>
        </div>
        <a href="<?= base_url('hewan/tambah.php') ?>" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Tambah Hewan Baru
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-custom table-hover mb-0">
            <thead>
                <tr>
                    <th style="width: 60px;">ID</th>
                    <th>Nama & Jenis Hewan</th>
                    <th>Ras</th>
                    <th>Pemilik (Foreign Key)</th>
                    <th>Umur</th>
                    <th>Kelamin</th>
                    <th>Berat</th>
                    <th class="text-center" style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $totalRows > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><span class="badge bg-light text-secondary border">#<?= $row['id_hewan'] ?></span></td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($row['nama_hewan']) ?></div>
                                <span class="badge badge-pet-<?= strtolower($row['jenis_hewan']) ?>">
                                    <?= htmlspecialchars($row['jenis_hewan']) ?>
                                </span>
                            </td>
                            <td><span class="text-secondary"><?= htmlspecialchars($row['ras']) ?></span></td>
                            <td>
                                <div class="fw-semibold text-primary">
                                    <i class="bi bi-person me-1"></i><?= htmlspecialchars($row['nama_pemilik']) ?>
                                </div>
                                <small class="text-muted"><?= htmlspecialchars($row['no_hp']) ?></small>
                            </td>
                            <td><?= $row['umur_bulan'] ?> Bulan <small class="text-muted">(<?= round($row['umur_bulan'] / 12, 1) ?> Thn)</small></td>
                            <td>
                                <?php if ($row['jenis_kelamin'] === 'Jantan'): ?>
                                    <span class="badge bg-primary-subtle text-primary"><i class="bi bi-gender-male me-1"></i> Jantan</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger"><i class="bi bi-gender-female me-1"></i> Betina</span>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= number_format($row['berat_kg'], 2) ?></strong> kg</td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= base_url('hewan/edit.php?id=' . $row['id_hewan']) ?>" class="btn btn-outline-warning" title="Edit Data">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="<?= base_url('hewan/hapus.php?id=' . $row['id_hewan']) ?>" 
                                       class="btn btn-outline-danger btn-delete-confirm" 
                                       data-name="<?= htmlspecialchars($row['nama_hewan']) ?> (<?= htmlspecialchars($row['jenis_hewan']) ?>)"
                                       title="Hapus Data">
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
                            Tidak ada data hewan yang sesuai kriteria pencarian.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

