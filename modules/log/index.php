<?php
require_once '../../config/constants.php';
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Cek login
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit();
}

// Cek role admin
if ($_SESSION['role'] !== 'admin') {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}

$pageTitle = 'Log Aktivitas';
$db = (new Database())->getConnection();

// Drop tabel log_aktivitas jika ada (untuk membuat ulang)
try {
    // Pastikan tabel users ada terlebih dahulu
    $createUsersTable = "CREATE TABLE IF NOT EXISTS users (
        id_user INT(11) AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        nama_lengkap VARCHAR(100) NOT NULL,
        role ENUM('admin', 'operator') NOT NULL DEFAULT 'operator',
        email VARCHAR(100) NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_username (username),
        INDEX idx_role (role)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $db->exec($createUsersTable);
    
    // Hapus tabel log jika ada
    $db->exec("DROP TABLE IF EXISTS log_aktivitas");
    
    // Buat ulang tabel log_aktivitas sesuai struktur yang ada
    $createLogTable = "CREATE TABLE IF NOT EXISTS log_aktivitas (
        id_log INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) NOT NULL,
        aktivitas ENUM('TAMBAH', 'EDIT', 'HAPUS') NOT NULL,
        keterangan TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        CONSTRAINT fk_log_user FOREIGN KEY (user_id) REFERENCES users(id_user) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $db->exec($createLogTable);
    
    // Cek apakah sudah ada admin
    $checkAdmin = "SELECT COUNT(*) as count FROM users WHERE role = 'admin'";
    $result = $db->query($checkAdmin)->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] == 0) {
        // Tambahkan admin default jika belum ada
        $hashedPassword = password_hash('admin12345', PASSWORD_DEFAULT);
        $insertAdmin = "INSERT INTO users (username, password, nama_lengkap, email, role) 
                       VALUES ('admin', :password, 'Administrator', 'admin@sipadu.com', 'admin')";
        $stmt = $db->prepare($insertAdmin);
        $stmt->execute(['password' => $hashedPassword]);
    }
    
} catch(PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    die("Terjadi kesalahan saat menyiapkan database. Silakan hubungi administrator.");
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Filter
$filter_user = isset($_GET['user']) ? $_GET['user'] : '';
$filter_action = isset($_GET['action']) ? $_GET['action'] : '';
$filter_date = isset($_GET['date']) ? $_GET['date'] : '';

// Build query
$where = [];
$params = [];

if ($filter_user) {
    $where[] = "(u.username LIKE ? OR u.nama_lengkap LIKE ?)";
    $params[] = "%$filter_user%";
    $params[] = "%$filter_user%";
}

if ($filter_action) {
    $where[] = "l.aktivitas = ?";
    $params[] = $filter_action;
}

if ($filter_date) {
    $where[] = "DATE(l.created_at) = ?";
    $params[] = $filter_date;
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

try {
    // Get total records for pagination
    $countQuery = "SELECT COUNT(*) as total FROM log_aktivitas l 
                   LEFT JOIN users u ON l.user_id = u.id_user 
                   $whereClause";
    $stmt = $db->prepare($countQuery);
    $stmt->execute($params);
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($total / $limit);

    // Get log data
    $query = "SELECT l.*, u.username, u.nama_lengkap 
              FROM log_aktivitas l 
              LEFT JOIN users u ON l.user_id = u.id_user 
              $whereClause 
              ORDER BY l.created_at DESC 
              LIMIT ? OFFSET ?";

    $stmt = $db->prepare($query);
    $params[] = $limit;
    $params[] = $offset;
    $stmt->execute($params);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get unique actions for filter
    $actionQuery = "SELECT DISTINCT aktivitas FROM log_aktivitas ORDER BY aktivitas";
    $stmt = $db->prepare($actionQuery);
    $stmt->execute();
    $actions = $stmt->fetchAll(PDO::FETCH_COLUMN);

} catch(PDOException $e) {
    error_log("Query Error: " . $e->getMessage());
    $total = 0;
    $totalPages = 0;
    $logs = [];
    $actions = [];
}

include '../../includes/header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4"><?php echo $pageTitle; ?></h1>
    
    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">User</label>
                    <input type="text" name="user" class="form-control" value="<?php echo htmlspecialchars($filter_user); ?>" placeholder="Username atau nama">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Aktivitas</label>
                    <select name="action" class="form-select">
                        <option value="">Semua</option>
                        <?php foreach ($actions as $action): ?>
                            <option value="<?php echo htmlspecialchars($action); ?>" <?php echo $filter_action === $action ? 'selected' : ''; ?>>
                                <?php echo $action; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($filter_date); ?>">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                    <a href="index.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Log Table -->
    <div class="card mb-4">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>User</th>
                        <th>Aktivitas</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($log['nama_lengkap'] . ' (' . $log['username'] . ')'); ?></td>
                            <td><?php echo htmlspecialchars($log['aktivitas']); ?></td>
                            <td><?php echo htmlspecialchars($log['keterangan']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="4" class="text-center">Tidak ada data log aktivitas</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav>
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo $page === $i ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo $filter_user ? '&user=' . urlencode($filter_user) : ''; ?><?php echo $filter_action ? '&action=' . urlencode($filter_action) : ''; ?><?php echo $filter_date ? '&date=' . urlencode($filter_date) : ''; ?>">
                                    <?php echo $i; ?>
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
