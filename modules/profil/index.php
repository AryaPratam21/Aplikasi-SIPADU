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

$pageTitle = 'Profil Pengguna';
$db = (new Database())->getConnection();

// Cek dan buat database jika belum ada
try {
    $db->exec("CREATE DATABASE IF NOT EXISTS sipadu");
    $db->exec("USE sipadu");
    
    // Cek dan buat tabel users jika belum ada
    $createTable = "CREATE TABLE IF NOT EXISTS users (
        id_user INT(11) AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        nama_lengkap VARCHAR(100) NOT NULL,
        role ENUM('admin', 'operator') NOT NULL DEFAULT 'operator',
        email VARCHAR(100) NULL DEFAULT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_username (username),
        INDEX idx_role (role)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $db->exec($createTable);

    // Buat tabel log_aktivitas jika belum ada
    $createLogTable = "CREATE TABLE IF NOT EXISTS log_aktivitas (
        id_log INT(11) AUTO_INCREMENT PRIMARY KEY,
        id_user INT(11) NOT NULL,
        aktivitas VARCHAR(255) NOT NULL,
        tabel VARCHAR(50) NULL,
        jenis_aksi ENUM('login', 'logout', 'create', 'update', 'delete', 'view') NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        ip_address VARCHAR(45) NULL,
        user_agent VARCHAR(255) NULL,
        FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE,
        INDEX idx_user (id_user),
        INDEX idx_jenis_aksi (jenis_aksi),
        INDEX idx_created_at (created_at)
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

// Ambil data user yang sedang login
$userId = $_SESSION['user_id'];
$userData = [];

try {
    $query = "SELECT username, nama_lengkap, role, email, created_at, updated_at FROM users WHERE id_user = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$userId]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
}

// Proses update profil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $errors = [];

    // Validasi input
    if (empty($username)) {
        $errors[] = "Username tidak boleh kosong";
    }
    if (empty($nama_lengkap)) {
        $errors[] = "Nama lengkap tidak boleh kosong";
    }
    if (empty($email)) {
        $errors[] = "Email tidak boleh kosong";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid";
    }

    // Cek username unik
    if (!empty($username)) {
        $query = "SELECT COUNT(*) as count FROM users WHERE username = ? AND id_user != ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$username, $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result['count'] > 0) {
            $errors[] = "Username sudah digunakan";
        }
    }

    // Jika password diisi, validasi panjang minimal
    if (!empty($password) && strlen($password) < 6) {
        $errors[] = "Password minimal 6 karakter";
    }

    // Update data jika tidak ada error
    if (empty($errors)) {
        try {
            if (!empty($password)) {
                // Update dengan password baru
                $query = "UPDATE users SET username = ?, nama_lengkap = ?, email = ?, password = ?, updated_at = NOW() WHERE id_user = ?";
                $stmt = $db->prepare($query);
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt->execute([$username, $nama_lengkap, $email, $hashedPassword, $userId]);
                
                // Log aktivitas update password
                logAktivitas($db, $userId, 'Mengubah password', 'update', 'users');
            } else {
                // Update tanpa password
                $query = "UPDATE users SET username = ?, nama_lengkap = ?, email = ?, updated_at = NOW() WHERE id_user = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([$username, $nama_lengkap, $email, $userId]);
            }
            
            // Log aktivitas update profil
            logAktivitas($db, $userId, 'Mengubah profil', 'update', 'users');
            
            // Update session data
            $_SESSION['username'] = $username;
            $_SESSION['nama_lengkap'] = $nama_lengkap;
            $_SESSION['success'] = "Profil berhasil diperbarui";
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        } catch(PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            $errors[] = "Terjadi kesalahan saat memperbarui profil";
        }
    }
}

include '../../includes/header.php';
?>

<div class="container px-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-user me-2"></i>Profil Pengguna
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= $error ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <?= $_SESSION['success'] ?>
                            <?php unset($_SESSION['success']); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?= htmlspecialchars($userData['username'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" 
                                   value="<?= htmlspecialchars($userData['nama_lengkap'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($userData['email'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <input type="text" class="form-control" id="role" 
                                   value="<?= htmlspecialchars($userData['role'] ?? '') ?>" readonly>
                            <div class="form-text">Role tidak dapat diubah</div>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password Baru</label>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Kosongkan jika tidak ingin mengubah password">
                            <div class="form-text">Minimal 6 karakter</div>
                        </div>

                        <div class="mb-4">
                            <h6 class="mb-3">Informasi Tambahan:</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="small text-muted mb-2">
                                        <i class="fas fa-clock me-1"></i>Waktu Pembuatan Akun:
                                        <br>
                                        <span class="ms-4">
                                            <?php 
                                            if (isset($userData['created_at']) && $userData['created_at']) {
                                                echo date('d F Y H:i:s', strtotime($userData['created_at']));
                                            } else {
                                                echo 'Tidak tersedia';
                                            }
                                            ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="small text-muted mb-2">
                                        <i class="fas fa-edit me-1"></i>Terakhir Diperbarui:
                                        <br>
                                        <span class="ms-4">
                                            <?php 
                                            if (isset($userData['updated_at']) && $userData['updated_at']) {
                                                echo date('d F Y H:i:s', strtotime($userData['updated_at']));
                                            } else {
                                                echo 'Belum pernah diperbarui';
                                            }
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Simpan Perubahan
                            </button>
                            <a href="<?= BASE_URL ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
