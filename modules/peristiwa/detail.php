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

$pageTitle = 'Detail Peristiwa';

// Inisialisasi database
$db = new Database();
$conn = $db->getConnection();

// Ambil data peristiwa
try {
    $id = (int)$_GET['id'];
    $query = "SELECT p.*, pd.nama as nama_penduduk, pd.nik 
              FROM peristiwa p
              JOIN penduduk pd ON p.id_penduduk = pd.id_penduduk
              WHERE p.id_peristiwa = :id";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([':id' => $id]);
    $data = $stmt->fetch();

    if (!$data) {
        $_SESSION['error'] = "Data peristiwa tidak ditemukan";
        header('Location: index.php');
        exit();
    }
} catch(PDOException $e) {
    error_log($e->getMessage());
    $_SESSION['error'] = "Terjadi kesalahan saat mengambil data";
    header('Location: index.php');
    exit();
}

include '../../includes/header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4"><?= $pageTitle ?></h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="../../index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php">Peristiwa</a></li>
        <li class="breadcrumb-item active">Detail</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-info-circle me-1"></i>
            Detail Peristiwa
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <tr>
                    <th width="200">NIK</th>
                    <td><?= $data['nik'] ?></td>
                </tr>
                <tr>
                    <th>Nama</th>
                    <td><?= $data['nama_penduduk'] ?></td>
                </tr>
                <tr>
                    <th>Jenis Peristiwa</th>
                    <td><?= $data['jenis_peristiwa'] ?></td>
                </tr>
                <tr>
                    <th>Tanggal Peristiwa</th>
                    <td><?= date('d/m/Y', strtotime($data['tanggal_peristiwa'])) ?></td>
                </tr>
                <tr>
                    <th>Keterangan</th>
                    <td><?= $data['keterangan'] ?></td>
                </tr>
                <tr>
                    <th>Tanggal Input</th>
                    <td><?= date('d/m/Y H:i:s', strtotime($data['created_at'])) ?></td>
                </tr>
            </table>
            <div class="mt-3">
                <a href="index.php" class="btn btn-secondary">Kembali</a>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>