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

$pageTitle = 'Tambah Kartu Keluarga';
$error = '';

// Inisialisasi database
$db = new Database();
$conn = $db->getConnection();

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Validasi input
        $no_kk = cleanInput($_POST['no_kk']);
        $kepala_keluarga = cleanInput($_POST['kepala_keluarga']);
        $alamat = cleanInput($_POST['alamat']);
        
        // Validasi No KK
        if (strlen($no_kk) !== 16) {
            throw new Exception("Nomor KK harus 16 digit");
        }
        
        // Cek No KK duplikat
        $checkKK = "SELECT COUNT(*) FROM keluarga WHERE no_kk = ?";
        $stmtCheck = $conn->prepare($checkKK);
        $stmtCheck->execute([$no_kk]);
        if ($stmtCheck->fetchColumn() > 0) {
            throw new Exception("Nomor KK sudah terdaftar");
        }
        
        // Begin transaction
        $conn->beginTransaction();
        
        // Insert data keluarga
        $query = "INSERT INTO keluarga (no_kk, kepala_keluarga, alamat) 
                 VALUES (:no_kk, :kepala_keluarga, :alamat)";
        
        $stmt = $conn->prepare($query);
        $stmt->execute([
            ':no_kk' => $no_kk,
            ':kepala_keluarga' => $kepala_keluarga,
            ':alamat' => $alamat
        ]);
        
        // Catat log aktivitas
        $logQuery = "INSERT INTO log_aktivitas (user_id, aktivitas, keterangan) 
                    VALUES (:user_id, 'TAMBAH', :keterangan)";
        $logStmt = $conn->prepare($logQuery);
        $logStmt->execute([
            ':user_id' => $_SESSION['user_id'],
            ':keterangan' => "Menambah kartu keluarga: $no_kk"
        ]);
        
        // Commit transaction
        $conn->commit();
        
        $_SESSION['success'] = "Data kartu keluarga berhasil ditambahkan";
        redirect('modules/keluarga/index.php');
        exit();
        
    } catch(Exception $e) {
        // Rollback transaction jika terjadi error
        $conn->rollBack();
        error_log($e->getMessage());
        $error = $e->getMessage();
    }
}

include '../../includes/header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4"><?= $pageTitle ?></h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="../../index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php">Kartu Keluarga</a></li>
        <li class="breadcrumb-item active">Tambah KK</li>
    </ol>

    <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $error ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-plus me-1"></i>
            Form Tambah Kartu Keluarga
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="no_kk" class="form-label">Nomor KK</label>
                    <input type="text" class="form-control" id="no_kk" name="no_kk" required 
                           maxlength="16" pattern="[0-9]{16}" title="Nomor KK harus 16 digit angka">
                </div>
                <div class="mb-3">
                    <label for="kepala_keluarga" class="form-label">Kepala Keluarga</label>
                    <input type="text" class="form-control" id="kepala_keluarga" name="kepala_keluarga" required>
                </div>
                <div class="mb-3">
                    <label for="alamat" class="form-label">Alamat</label>
                    <textarea class="form-control" id="alamat" name="alamat" rows="3" required></textarea>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="index.php" class="btn btn-secondary">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?> 