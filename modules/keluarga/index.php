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

$pageTitle = 'Data Kartu Keluarga';

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
    $whereClause = "WHERE no_kk LIKE :search OR kepala_keluarga LIKE :search";
}

// Query untuk menghitung total data
$countQuery = "SELECT COUNT(*) as total FROM keluarga $whereClause";
$countStmt = $conn->prepare($countQuery);
if ($search) {
    $searchParam = "%$search%";
    $countStmt->bindParam(':search', $searchParam);
}
$countStmt->execute();
$totalData = $countStmt->fetch()['total'];
$totalPages = ceil($totalData / $limit);

// Query untuk mengambil data keluarga
$query = "SELECT k.* FROM keluarga k 
          $whereClause 
          ORDER BY k.created_at DESC 
          LIMIT :limit OFFSET :offset";

$stmt = $conn->prepare($query);
if ($search) {
    $stmt->bindParam(':search', $searchParam);
}
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

include '../../includes/header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4"><?= $pageTitle ?></h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="../../index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Kartu Keluarga</li>
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
                    Data Kartu Keluarga
                </div>
                <a href="tambah.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i>Tambah KK
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="mb-3">
                <div class="input-group">
                    <input type="text" class="form-control" name="search" 
                           placeholder="Cari berdasarkan No KK atau Kepala Keluarga..." 
                           value="<?= htmlspecialchars($search) ?>">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="fas fa-search"></i> Cari
                    </button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>No KK</th>
                            <th>Kepala Keluarga</th>
                            <th>Alamat</th>
                            <th>Tanggal Update</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = $offset + 1;
                        while ($row = $stmt->fetch()): 
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['no_kk']) ?></td>
                            <td><?= htmlspecialchars($row['kepala_keluarga']) ?></td>
                            <td><?= htmlspecialchars($row['alamat']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($row['updated_at'])) ?></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-info btn-sm" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#detailModal<?= $row['id_keluarga'] ?>" 
                                            title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <a href="edit.php?id=<?= $row['id_keluarga'] ?>" 
                                       class="btn btn-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="hapus.php?id=<?= $row['id_keluarga'] ?>" 
                                       class="btn btn-danger btn-sm" 
                                       onclick="return confirm('Yakin ingin menghapus data ini? Semua data anggota keluarga juga akan terhapus.')"
                                       title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>

                                <!-- Modal Detail -->
                                <div class="modal fade" id="detailModal<?= $row['id_keluarga'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Detail Kartu Keluarga</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <table class="table table-bordered">
                                                    <tr>
                                                        <th width="200">ID Keluarga</th>
                                                        <td><?= htmlspecialchars($row['id_keluarga']) ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Nomor KK</th>
                                                        <td><?= htmlspecialchars($row['no_kk']) ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Kepala Keluarga</th>
                                                        <td><?= htmlspecialchars($row['kepala_keluarga']) ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Alamat</th>
                                                        <td><?= nl2br(htmlspecialchars($row['alamat'])) ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Tanggal Dibuat</th>
                                                        <td><?= date('d F Y H:i', strtotime($row['created_at'])) ?></td>
                                                    </tr>
                                                </table>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        
                        <?php if ($stmt->rowCount() == 0): ?>
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada data</td>
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