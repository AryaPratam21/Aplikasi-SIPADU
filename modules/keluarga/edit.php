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

$pageTitle = 'Edit Kartu Keluarga';
$error = '';
$keluarga = null;

// Inisialisasi database
$db = new Database();
$conn = $db->getConnection();

// Ambil data keluarga yang akan diedit
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    try {
        $query = "SELECT * FROM keluarga WHERE id_keluarga = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $keluarga = $stmt->fetch();
        } else {
            $_SESSION['error'] = "Data keluarga tidak ditemukan";
            redirect('modules/keluarga/index.php');
            exit();
        }
    } catch(PDOException $e) {
        error_log($e->getMessage());
        $_SESSION['error'] = "Terjadi kesalahan saat mengambil data";
        redirect('modules/keluarga/index.php');
        exit();
    }
}

// Proses update data
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
        
        // Cek No KK duplikat kecuali untuk ID yang sedang diedit
        $checkKK = "SELECT COUNT(*) FROM keluarga WHERE no_kk = ? AND id_keluarga != ?";
        $stmtCheck = $conn->prepare($checkKK);
        $stmtCheck->execute([$no_kk, $id]);
        if ($stmtCheck->fetchColumn() > 0) {
            throw new Exception("Nomor KK sudah terdaftar untuk keluarga lain");
        }
        
        // Begin transaction
        $conn->beginTransaction();
        
        // Update data keluarga
        $query = "UPDATE keluarga SET 
                  no_kk = :no_kk,
                  kepala_keluarga = :kepala_keluarga,
                  alamat = :alamat
                  WHERE id_keluarga = :id";
        
        $stmt = $conn->prepare($query);
        $stmt->execute([
            ':no_kk' => $no_kk,
            ':kepala_keluarga' => $kepala_keluarga,
            ':alamat' => $alamat,
            ':id' => $id
        ]);
        
        // Catat log aktivitas
        $logQuery = "INSERT INTO log_aktivitas (user_id, aktivitas, keterangan) 
                    VALUES (:user_id, 'EDIT', :keterangan)";
        $logStmt = $conn->prepare($logQuery);
        $logStmt->execute([
            ':user_id' => $_SESSION['user_id'],
            ':keterangan' => "Mengubah data kartu keluarga: $no_kk"
        ]);
        
        // Commit transaction
        $conn->commit();
        
        $_SESSION['success'] = "Data kartu keluarga berhasil diperbarui";
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
        <li class="breadcrumb-item active">Edit KK</li>
    </ol>

    <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $error ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-edit me-1"></i>
            Form Edit Kartu Keluarga
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="no_kk" class="form-label">Nomor KK</label>
                    <input type="text" class="form-control" id="no_kk" name="no_kk" required 
                           maxlength="16" pattern="[0-9]{16}" title="Nomor KK harus 16 digit angka"
                           value="<?= htmlspecialchars($keluarga['no_kk']) ?>">
                </div>
                <div class="mb-3">
                    <label for="kepala_keluarga" class="form-label">Kepala Keluarga</label>
                    <input type="text" class="form-control" id="kepala_keluarga" name="kepala_keluarga" required
                           value="<?= htmlspecialchars($keluarga['kepala_keluarga']) ?>">
                </div>
                <div class="mb-3">
                    <label for="alamat" class="form-label">Alamat</label>
                    <textarea class="form-control" id="alamat" name="alamat" rows="3" required><?= htmlspecialchars($keluarga['alamat']) ?></textarea>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="index.php" class="btn btn-secondary">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?> 