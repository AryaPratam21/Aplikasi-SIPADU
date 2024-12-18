<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('/auth/login.php');
}

$db = (new Database())->getConnection();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 50; // Tampilkan 50 log per halaman
$offset = ($page - 1) * $limit;

// Filter
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : '';
$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : '';
$jenis = isset($_GET['jenis']) ? cleanInput($_GET['jenis']) : '';

// Build query
$whereClause = [];
$params = [];

if ($user_id) {
    $whereClause[] = "l.user_id = :user_id";
    $params[':user_id'] = $user_id;
}

if ($tanggal) {
    $whereClause[] = "DATE(l.created_at) = :tanggal";
    $params[':tanggal'] = $tanggal;
}

if ($jenis) {
    $whereClause[] = "l.jenis_aktivitas LIKE :jenis";
    $params[':jenis'] = "%$jenis%";
}

$where = '';
if (!empty($whereClause)) {
    $where = "WHERE " . implode(" AND ", $whereClause);
}

// Query untuk menghitung total data
$countQuery = "SELECT COUNT(*) as total FROM log_aktivitas l $where";
$countStmt = $db->prepare($countQuery);
foreach($params as $key => $value) {
    $countStmt->bindValue($key, $value);
}
$countStmt->execute();
$totalData = $countStmt->fetch()['total'];
$totalPages = ceil($totalData / $limit);

// Query untuk mengambil data log
$query = "SELECT l.*, u.username, u.nama_lengkap 
          FROM log_aktivitas l 
          LEFT JOIN users u ON l.user_id = u.id 
          $where 
          ORDER BY l.created_at DESC 
          LIMIT :limit OFFSET :offset";

$stmt = $db->prepare($query);
foreach($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

// Ambil daftar user untuk filter
$queryUsers = "SELECT id, username, nama_lengkap FROM users ORDER BY username";
$stmtUsers = $db->query($queryUsers);
$users = $stmtUsers->fetchAll();

include '../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3>Log Aktivitas</h3>
    </div>
    <div class="card-body">
        <!-- Form Filter -->
        <form method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label>User</label>
                    <select name="user_id" class="form-control">
                        <option value="">Semua User</option>
                        <?php foreach($users as $user): ?>
                            <option value="<?= $user['id'] ?>" 
                                    <?= $user_id == $user['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($user['username'] . ' - ' . $user['nama_lengkap']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" 
                           value="<?= htmlspecialchars($tanggal) ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label>Jenis Aktivitas</label>
                    <select name="jenis" class="form-control">
                        <option value="">Semua Aktivitas</option>
                        <option value="login" <?= $jenis == 'login' ? 'selected' : '' ?>>Login</option>
                        <option value="logout" <?= $jenis == 'logout' ? 'selected' : '' ?>>Logout</option>
                        <option value="tambah" <?= $jenis == 'tambah' ? 'selected' : '' ?>>Tambah Data</option>
                        <option value="update" <?= $jenis == 'update' ? 'selected' : '' ?>>Update Data</option>
                        <option value="hapus" <?= $jenis == 'hapus' ? 'selected' : '' ?>>Hapus Data</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label>&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="log_aktivitas.php" class="btn btn-secondary">Reset</a>
                    </div>
                </div>
            </div>
        </form>

        <!-- Tabel Log -->
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>User</th>
                        <th>IP Address</th>
                        <th>Aktivitas</th>
                        <th>Detail</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $stmt->fetch()): ?>
                    <tr>
                        <td><?= date('d/m/Y H:i:s', strtotime($row['created_at'])) ?></td>
                        <td>
                            <?= $row['username'] ? 
                                htmlspecialchars($row['username'] . ' - ' . $row['nama_lengkap']) : 
                                '<span class="text-muted">User tidak ditemukan</span>' ?>
                        </td>
                        <td><?= htmlspecialchars($row['ip_address']) ?></td>
                        <td><?= htmlspecialchars($row['jenis_aktivitas']) ?></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-info" 
                                    onclick="showDetail('<?= htmlspecialchars($row['keterangan']) ?>')">
                                Detail
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?><?= 
                        $user_id ? '&user_id=' . $user_id : '' ?><?= 
                        $tanggal ? '&tanggal=' . $tanggal : '' ?><?= 
                        $jenis ? '&jenis=' . urlencode($jenis) : '' 
                    ?>">
                        <?= $i ?>
                    </a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Aktivitas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <pre id="detailContent" class="bg-light p-3" style="white-space: pre-wrap;"></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
function showDetail(detail) {
    document.getElementById('detailContent').textContent = detail;
    new bootstrap.Modal(document.getElementById('detailModal')).show();
}
</script>

<?php include '../../includes/footer.php'; ?>
