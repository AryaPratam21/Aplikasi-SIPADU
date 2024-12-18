<?php
require_once '../../config/constants.php';
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Cek login
if (!isLoggedIn()) {
    header('Location: ../../auth/login.php');
    exit();
}

$pageTitle = 'Modul Laporan';

// Inisialisasi variabel
$stats = [
    'total_penduduk' => 0,
    'total_keluarga' => 0,
    'total_laki' => 0,
    'total_perempuan' => 0
];
$error = null;

try {
    $db = new Database();
    $conn = $db->getConnection();

    if (!$conn) {
        throw new Exception("Koneksi database gagal");
    }

    // Query untuk total penduduk
    $queryPenduduk = "SELECT COUNT(*) as total FROM penduduk";
    $stmt = $conn->query($queryPenduduk);
    if ($stmt) {
        $stats['total_penduduk'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // Query untuk total keluarga
    $queryKeluarga = "SELECT COUNT(*) as total FROM keluarga";
    $stmt = $conn->query($queryKeluarga);
    if ($stmt) {
        $stats['total_keluarga'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // Query untuk total penduduk laki-laki
    $queryLaki = "SELECT COUNT(*) as total FROM penduduk WHERE jenis_kelamin = 'L'";
    $stmt = $conn->query($queryLaki);
    if ($stmt) {
        $stats['total_laki'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // Query untuk total penduduk perempuan
    $queryPerempuan = "SELECT COUNT(*) as total FROM penduduk WHERE jenis_kelamin = 'P'";
    $stmt = $conn->query($queryPerempuan);
    if ($stmt) {
        $stats['total_perempuan'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

} catch(PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    $error = "Terjadi kesalahan pada database. Silakan coba beberapa saat lagi.";
} catch(Exception $e) {
    error_log("General Error: " . $e->getMessage());
    $error = "Terjadi kesalahan. Silakan coba beberapa saat lagi.";
}

include '../../includes/header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4"><?= $pageTitle ?></h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="../../index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Modul Laporan</li>
    </ol>

    <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-1"></i>
        <?= $error ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <!-- Menu Cards -->
    <div class="row justify-content-center mb-4">
        <div class="col-lg-5 col-md-6 mb-4">
            <a href="cetak.php" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <div class="mb-3">
                            <i class="fas fa-print fa-4x text-primary"></i>
                        </div>
                        <h4 class="card-title">Cetak Laporan</h4>
                        <p class="card-text text-muted">
                            Cetak laporan data penduduk, keluarga, dan peristiwa berdasarkan periode tertentu
                        </p>
                        <div class="mt-4">
                            <span class="btn btn-primary">
                                <i class="fas fa-arrow-right me-1"></i>
                                Buka Menu Cetak
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-5 col-md-6 mb-4">
            <a href="statistik.php" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <div class="mb-3">
                            <i class="fas fa-chart-bar fa-4x text-success"></i>
                        </div>
                        <h4 class="card-title">Statistik Data</h4>
                        <p class="card-text text-muted">
                            Lihat statistik dan grafik data kependudukan secara detail
                        </p>
                        <div class="mt-4">
                            <span class="btn btn-success">
                                <i class="fas fa-arrow-right me-1"></i>
                                Buka Menu Statistik
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Statistics Summary -->
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-1"></i>
                    Ringkasan Data
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <div class="p-3">
                                <i class="fas fa-users fa-2x text-primary mb-2"></i>
                                <h2 class="text-primary mb-0"><?= number_format($stats['total_penduduk']) ?></h2>
                                <div class="text-muted small">Total Penduduk</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="p-3">
                                <i class="fas fa-home fa-2x text-success mb-2"></i>
                                <h2 class="text-success mb-0"><?= number_format($stats['total_keluarga']) ?></h2>
                                <div class="text-muted small">Total KK</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="p-3">
                                <i class="fas fa-male fa-2x text-info mb-2"></i>
                                <h2 class="text-info mb-0"><?= number_format($stats['total_laki']) ?></h2>
                                <div class="text-muted small">Laki-laki</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="p-3">
                                <i class="fas fa-female fa-2x text-warning mb-2"></i>
                                <h2 class="text-warning mb-0"><?= number_format($stats['total_perempuan']) ?></h2>
                                <div class="text-muted small">Perempuan</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    transition: transform 0.2s ease-in-out;
}
.card:hover {
    transform: translateY(-5px);
}
.card-body {
    position: relative;
    z-index: 1;
}
.btn {
    transition: all 0.2s ease-in-out;
}
.btn:hover {
    transform: translateX(5px);
}
.alert {
    border-radius: 0.5rem;
}
</style>

<?php include '../../includes/footer.php'; ?>
