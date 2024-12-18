<?php
require_once '../../config/constants.php';
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Cek login
if (!isLoggedIn()) {
    redirect('auth/login.php');
    exit();
}

$pageTitle = 'Data Penduduk';

// Inisialisasi database
$db = new Database();
$conn = $db->getConnection();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Pencarian
$search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
$whereClause = '';
if ($search) {
    $whereClause = "WHERE p.nama LIKE :search OR p.nik LIKE :search";
}

// Query untuk menghitung total data
$countQuery = "SELECT COUNT(*) as total FROM penduduk p $whereClause";
$countStmt = $conn->prepare($countQuery);
if ($search) {
    $searchParam = "%$search%";
    $countStmt->bindParam(':search', $searchParam);
}
$countStmt->execute();
$totalData = $countStmt->fetch()['total'];
$totalPages = ceil($totalData / $limit);

// Query untuk mengambil data penduduk dengan join ke keluarga
$query = "SELECT p.*, k.no_kk 
          FROM penduduk p 
          LEFT JOIN keluarga k ON p.id_keluarga = k.id_keluarga 
          $whereClause 
          ORDER BY p.created_at DESC 
          LIMIT :limit OFFSET :offset";

try {
    $stmt = $conn->prepare($query);
    if ($search) {
        $searchParam = "%$search%";
        $stmt->bindParam(':search', $searchParam);
    }
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    // Ambil semua data sekaligus
    $pendudukList = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    error_log("Error fetching penduduk: " . $e->getMessage());
    $_SESSION['error'] = "Terjadi kesalahan saat mengambil data";
}

include '../../includes/header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4"><?= $pageTitle ?></h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="../../index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Penduduk</li>
    </ol>

    <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $_SESSION['success'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success']); endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $_SESSION['error'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error']); endif; ?>

    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-table me-1"></i>
                    Data Penduduk
                </div>
                <a href="tambah.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i>Tambah Penduduk
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="mb-3">
                <div class="input-group">
                    <input type="text" class="form-control" name="search" 
                           placeholder="Cari berdasarkan nama atau NIK..." 
                           value="<?= htmlspecialchars($search) ?>">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="fas fa-search"></i> Cari
                    </button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>NIK</th>
                            <th>Nama</th>
                            <th>No KK</th>
                            <th>Jenis Kelamin</th>
                            <th>Tanggal Lahir</th>
                            <th>Alamat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = $offset + 1;
                        if (!empty($pendudukList)): 
                            foreach($pendudukList as $row): 
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['nik']) ?></td>
                            <td><?= htmlspecialchars($row['nama']) ?></td>
                            <td><?= htmlspecialchars($row['no_kk']) ?></td>
                            <td><?= $row['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan' ?></td>
                            <td><?= date('d/m/Y', strtotime($row['tanggal_lahir'])) ?></td>
                            <td><?= htmlspecialchars($row['alamat']) ?></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="detail.php?id=<?= $row['id_penduduk'] ?>" 
                                       class="btn btn-info btn-sm" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="edit.php?id=<?= $row['id_penduduk'] ?>" 
                                       class="btn btn-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="hapus.php?id=<?= $row['id_penduduk'] ?>" 
                                       class="btn btn-danger btn-sm" 
                                       onclick="return confirm('Yakin ingin menghapus data ini?')"
                                       title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php 
                            endforeach; 
                        else: 
                        ?>
                        <tr>
                            <td colspan="8" class="text-center">Tidak ada data</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

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

<?php include '../../includes/footer.php'; ?>
