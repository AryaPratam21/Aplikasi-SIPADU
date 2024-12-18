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

$pageTitle = 'Detail Kartu Keluarga';
$keluarga = null;

// Inisialisasi database
$db = new Database();
$conn = $db->getConnection();

// Ambil data keluarga
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

include '../../includes/header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4"><?= $pageTitle ?></h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="../../index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php">Kartu Keluarga</a></li>
        <li class="breadcrumb-item active">Detail</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-file-alt me-1"></i>
                    Detail Kartu Keluarga
                </div>
                <div>
                    <a href="edit.php?id=<?= $keluarga['id_keluarga'] ?>" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="index.php" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <tr>
                    <th width="200">ID Keluarga</th>
                    <td><?= htmlspecialchars($keluarga['id_keluarga']) ?></td>
                </tr>
                <tr>
                    <th>Nomor KK</th>
                    <td><?= htmlspecialchars($keluarga['no_kk']) ?></td>
                </tr>
                <tr>
                    <th>Kepala Keluarga</th>
                    <td><?= htmlspecialchars($keluarga['kepala_keluarga']) ?></td>
                </tr>
                <tr>
                    <th>Alamat</th>
                    <td><?= nl2br(htmlspecialchars($keluarga['alamat'])) ?></td>
                </tr>
                <tr>
                    <th>Tanggal Dibuat</th>
                    <td><?= date('d F Y H:i', strtotime($keluarga['created_at'])) ?></td>
                </tr>
                <tr>
                    <th>Terakhir Diupdate</th>
                    <td><?= date('d F Y H:i', strtotime($keluarga['updated_at'])) ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?> 