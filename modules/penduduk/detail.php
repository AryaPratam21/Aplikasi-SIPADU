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

$pageTitle = 'Detail Penduduk';
$penduduk = null;

// Inisialisasi database
try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Ambil data penduduk
    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        
        // Validasi ID
        if ($id <= 0) {
            throw new Exception("ID penduduk tidak valid");
        }
        
        // Query untuk mengambil data penduduk
        $query = "SELECT p.*, k.no_kk, k.kepala_keluarga 
                 FROM penduduk p 
                 LEFT JOIN keluarga k ON p.id_keluarga = k.id_keluarga 
                 WHERE p.id_penduduk = ? 
                 LIMIT 1";
        
        // Gunakan method fetchOne dari class Database
        $penduduk = $db->fetchOne($query, [$id]);
        
        if (!$penduduk) {
            throw new Exception("Data penduduk tidak ditemukan");
        }
        
    } else {
        throw new Exception("ID penduduk tidak ditemukan");
    }
    
} catch(PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    $_SESSION['error'] = "Terjadi kesalahan pada database";
    redirect('modules/penduduk/index.php');
    exit();
} catch(Exception $e) {
    error_log("Error: " . $e->getMessage());
    $_SESSION['error'] = $e->getMessage();
    redirect('modules/penduduk/index.php');
    exit();
}

include '../../includes/header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4"><?= $pageTitle ?></h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="../../index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php">Penduduk</a></li>
        <li class="breadcrumb-item active">Detail Penduduk</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-user me-1"></i>
                    Data Penduduk
                </div>
                <div>
                    <a href="index.php" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th width="200">NIK</th>
                            <td><?= htmlspecialchars($penduduk['nik']) ?></td>
                        </tr>
                        <tr>
                            <th>Nama Lengkap</th>
                            <td><?= htmlspecialchars($penduduk['nama']) ?></td>
                        </tr>
                        <tr>
                            <th>Jenis Kelamin</th>
                            <td><?= $penduduk['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan' ?></td>
                        </tr>
                        <tr>
                            <th>Tanggal Lahir</th>
                            <td><?= date('d F Y', strtotime($penduduk['tanggal_lahir'])) ?></td>
                        </tr>
                        <tr>
                            <th>Agama</th>
                            <td><?= htmlspecialchars($penduduk['agama']) ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th width="200">Status Perkawinan</th>
                            <td>
                                <?php
                                switch($penduduk['status_perkawinan']) {
                                    case 'BELUM KAWIN':
                                        echo 'Belum Kawin';
                                        break;
                                    case 'KAWIN':
                                        echo 'Kawin';
                                        break;
                                    case 'CERAI HIDUP':
                                        echo 'Cerai Hidup';
                                        break;
                                    case 'CERAI MATI':
                                        echo 'Cerai Mati';
                                        break;
                                    default:
                                        echo htmlspecialchars($penduduk['status_perkawinan']);
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Pekerjaan</th>
                            <td><?= htmlspecialchars($penduduk['pekerjaan']) ?></td>
                        </tr>
                        <tr>
                            <th>Nomor KK</th>
                            <td><?= htmlspecialchars($penduduk['no_kk']) ?></td>
                        </tr>
                        <tr>
                            <th>Kepala Keluarga</th>
                            <td><?= htmlspecialchars($penduduk['kepala_keluarga']) ?></td>
                        </tr>
                        <tr>
                            <th>Alamat</th>
                            <td><?= nl2br(htmlspecialchars($penduduk['alamat'])) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
