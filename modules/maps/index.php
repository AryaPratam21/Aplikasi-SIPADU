<?php
require_once '../../config/constants.php';
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Cek login
if (!isLoggedIn()) {
    redirect('../../auth/login.php');
    exit();
}

$pageTitle = 'Data Lokasi Penduduk';

// Inisialisasi database
$db = new Database();
$conn = $db->getConnection();

// Inisialisasi variabel
$totalData = 0;
$data = [];
$pendudukNoLoc = 0;
$totalPages = 0;

try {
    // Pagination
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;

    // Pencarian
    $search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
    $params = [];

    if ($search) {
        $params[':search'] = "%$search%";
    }

    // Query untuk menghitung total data
    $countQuery = "SELECT COUNT(*) as total 
                   FROM maps m 
                   JOIN penduduk p ON m.id_penduduk = p.id_penduduk 
                   " . ($search ? "WHERE p.nama LIKE :search OR p.nik LIKE :search" : "");
    
    $countStmt = $conn->prepare($countQuery);
    if ($search) {
        $countStmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    }
    $countStmt->execute();
    $totalData = (int)$countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalData / $limit);

    // Query untuk mengambil data maps
    $query = "SELECT m.*, p.nama, p.nik 
              FROM maps m 
              JOIN penduduk p ON m.id_penduduk = p.id_penduduk 
              " . ($search ? "WHERE p.nama LIKE :search OR p.nik LIKE :search" : "") . "
              ORDER BY m.created_at DESC 
              LIMIT :limit OFFSET :offset";

    $stmt = $conn->prepare($query);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    if ($search) {
        $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    }
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Debug query untuk memeriksa struktur tabel
    try {
        $debugQuery = "DESCRIBE penduduk";
        $debugStmt = $conn->query($debugQuery);
        $columns = $debugStmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Struktur tabel penduduk: " . print_r($columns, true));
    } catch(PDOException $e) {
        error_log("Error checking table structure: " . $e->getMessage());
    }

    // Hitung total penduduk yang belum memiliki lokasi
    $queryPendudukNoLoc = "SELECT COUNT(*) as total 
                          FROM penduduk p 
                          LEFT JOIN maps m ON p.id_penduduk = m.id_penduduk 
                          WHERE m.id_maps IS NULL";
    $stmtPendudukNoLoc = $conn->prepare($queryPendudukNoLoc);
    $stmtPendudukNoLoc->execute();
    $pendudukNoLoc = (int)$stmtPendudukNoLoc->fetch(PDO::FETCH_ASSOC)['total'];

} catch(PDOException $e) {
    error_log($e->getMessage());
    $_SESSION['error'] = "Terjadi kesalahan dalam mengambil data";
    $data = [];
}

include '../../includes/header.php';
?>

<!-- CSS Leaflet -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
#map { height: 400px; width: 100%; }
.info-box {
    padding: 10px;
    margin-bottom: 20px;
    border-radius: 5px;
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
}
</style>

<!-- Content -->
<div class="container-fluid px-4">
    <h1 class="mt-4"><?= $pageTitle ?></h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="../../index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Lokasi Penduduk</li>
    </ol>

    <?php 
    if (isset($_SESSION['success'])) {
        echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                {$_SESSION['success']}
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
              </div>";
        unset($_SESSION['success']);
    }
    if (isset($_SESSION['error'])) {
        echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                {$_SESSION['error']}
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
              </div>";
        unset($_SESSION['error']);
    }
    ?>

    <!-- Info Box -->
    <div class="info-box">
        <div class="row">
            <div class="col-md-6">
                <p class="mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    Total Lokasi Terdaftar: <strong><?= number_format($totalData) ?></strong>
                </p>
            </div>
            <div class="col-md-6">
                <p class="mb-0">
                    <i class="fas fa-user me-1"></i>
                    Penduduk Belum Memiliki Lokasi: <strong><?= number_format($pendudukNoLoc) ?></strong>
                </p>
            </div>
        </div>
    </div>

    <!-- Peta -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-map me-1"></i> Peta Lokasi Penduduk
        </div>
        <div class="card-body">
            <div id="map"></div>
        </div>
    </div>

    <!-- Tabel Data -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div><i class="fas fa-table me-1"></i> Data Lokasi Penduduk</div>
            <a href="<?= BASE_URL . '/modules/maps/tambah.php' ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i>Tambah Lokasi
            </a>
        </div>
        <div class="card-body">
            <!-- Form Pencarian -->
            <form method="GET" class="mb-3">
                <div class="input-group">
                    <input type="text" class="form-control" name="search" 
                           placeholder="Cari berdasarkan nama atau NIK..." 
                           value="<?= htmlspecialchars($search) ?>">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="fas fa-search"></i> Cari
                    </button>
                    <?php if ($search): ?>
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Reset
                    </a>
                    <?php endif; ?>
                </div>
            </form>

            <!-- Tabel -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NIK</th>
                            <th>Nama</th>
                            <th>Koordinat</th>
                            <th>Terakhir Update</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($data)): ?>
                            <?php 
                            $no = $offset + 1;
                            foreach($data as $row): 
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['nik']) ?></td>
                                <td><?= htmlspecialchars($row['nama']) ?></td>
                                <td>
                                    <?= $row['latitude'] ?>, <?= $row['longitude'] ?>
                                    <a href="https://www.google.com/maps?q=<?= $row['latitude'] ?>,<?= $row['longitude'] ?>" 
                                       target="_blank" class="btn btn-sm btn-info ms-2" title="Lihat di Google Maps">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </a>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($row['updated_at'])) ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="edit.php?id=<?= $row['id_maps'] ?>" 
                                           class="btn btn-warning btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="hapus.php?id=<?= $row['id_maps'] ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')" 
                                           title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">Tidak ada data</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <nav aria-label="Page navigation" class="mt-3">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" 
                           href="?page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?>">
                            <?= $i ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- JavaScript Leaflet -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Koordinat Jepara
    const JEPARA_CENTER = [-6.5888, 110.6684];
    const MIN_BOUNDS = [-6.7341, 110.5284]; // Batas Selatan-Barat
    const MAX_BOUNDS = [-6.4435, 110.8084]; // Batas Utara-Timur
    
    // Inisialisasi peta dengan koordinat Jepara
    var map = L.map('map', {
        center: JEPARA_CENTER,
        zoom: 12,
        minZoom: 11, // Batasi zoom out
        maxZoom: 18, // Batasi zoom in
        maxBounds: [MIN_BOUNDS, MAX_BOUNDS], // Batasi area pandangan
        maxBoundsViscosity: 1.0 // Mencegah drag keluar batas
    });
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Tambahkan marker untuk setiap lokasi
    <?php foreach($data as $row): ?>
    L.marker([<?= $row['latitude'] ?>, <?= $row['longitude'] ?>])
     .bindPopup(`
        <strong><?= htmlspecialchars($row['nama']) ?></strong><br>
        NIK: <?= htmlspecialchars($row['nik']) ?><br>
        <small>Koordinat: <?= $row['latitude'] ?>, <?= $row['longitude'] ?></small>
     `)
     .addTo(map);
    <?php endforeach; ?>
});
</script>

<?php include '../../includes/footer.php'; ?>