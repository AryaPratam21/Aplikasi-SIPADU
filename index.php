<?php
require_once 'config/constants.php';
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

// Cek login
if (!isLoggedIn()) {
    header('Location: auth/login.php');
    exit();
}

$pageTitle = 'Dashboard';
$db = (new Database())->getConnection();

// Inisialisasi statistik
$stats = [
    'total_penduduk' => 0,
    'total_keluarga' => 0,
    'total_laki' => 0,
    'total_perempuan' => 0,
    'total_lokasi' => 0,
    'penduduk_baru' => 0,
    'keluarga_baru' => 0,
    'lokasi_baru' => 0,
    'kelahiran' => 0,
    'kematian' => 0,
    'total_peristiwa' => 0,
    'peristiwa_baru' => 0,
    'pindah' => 0,
    'datang' => 0
];

try {
    // Total Penduduk
    $query = "SELECT COUNT(*) as total FROM penduduk";
    $stmt = $db->query($query);
    if ($stmt) {
        $stats['total_penduduk'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // Total Keluarga
    $query = "SELECT COUNT(*) as total FROM keluarga";
    $stmt = $db->query($query);
    if ($stmt) {
        $stats['total_keluarga'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // Total Laki-laki
    $query = "SELECT COUNT(*) as total FROM penduduk WHERE jenis_kelamin = 'L'";
    $stmt = $db->query($query);
    if ($stmt) {
        $stats['total_laki'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // Total Perempuan
    $query = "SELECT COUNT(*) as total FROM penduduk WHERE jenis_kelamin = 'P'";
    $stmt = $db->query($query);
    if ($stmt) {
        $stats['total_perempuan'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // Total Lokasi
    $query = "SELECT COUNT(*) as total FROM maps";
    $stmt = $db->query($query);
    if ($stmt) {
        $stats['total_lokasi'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // Data Baru (30 hari terakhir)
    $query = "SELECT COUNT(*) as total FROM penduduk WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    $stmt = $db->query($query);
    if ($stmt) {
        $stats['penduduk_baru'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    $query = "SELECT COUNT(*) as total FROM keluarga WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    $stmt = $db->query($query);
    if ($stmt) {
        $stats['keluarga_baru'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    $query = "SELECT COUNT(*) as total FROM maps WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    $stmt = $db->query($query);
    if ($stmt) {
        $stats['lokasi_baru'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // Statistik Peristiwa
    $query = "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN jenis_peristiwa = 'KELAHIRAN' THEN 1 ELSE 0 END) as kelahiran,
        SUM(CASE WHEN jenis_peristiwa = 'KEMATIAN' THEN 1 ELSE 0 END) as kematian,
        SUM(CASE WHEN jenis_peristiwa = 'PINDAH' THEN 1 ELSE 0 END) as pindah,
        SUM(CASE WHEN jenis_peristiwa = 'DATANG' THEN 1 ELSE 0 END) as datang
    FROM peristiwa";
    $stmt = $db->query($query);
    if ($stmt) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_peristiwa'] = (int)$row['total'];
        $stats['kelahiran'] = (int)$row['kelahiran'];
        $stats['kematian'] = (int)$row['kematian'];
        $stats['pindah'] = (int)$row['pindah'];
        $stats['datang'] = (int)$row['datang'];
    }

    // Peristiwa Baru (30 hari terakhir)
    $query = "SELECT COUNT(*) as total FROM peristiwa WHERE tanggal_peristiwa >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    $stmt = $db->query($query);
    if ($stmt) {
        $stats['peristiwa_baru'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

} catch(PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
}

include 'includes/header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Dashboard</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Dashboard</li>
    </ol>

    <!-- Statistik Utama -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="small text-white-50">Total Penduduk</div>
                            <div class="display-6"><?= number_format($stats['total_penduduk']) ?></div>
                        </div>
                        <div class="fa-3x">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <div class="small text-white">
                        <i class="fas fa-plus me-1"></i>
                        <?= number_format($stats['penduduk_baru']) ?> penduduk baru bulan ini
                    </div>
                    <a class="small text-white" href="modules/penduduk/index.php">
                        <i class="fas fa-angle-right"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="small text-white-50">Total KK</div>
                            <div class="display-6"><?= number_format($stats['total_keluarga']) ?></div>
                        </div>
                        <div class="fa-3x">
                            <i class="fas fa-home"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <div class="small text-white">
                        <i class="fas fa-plus me-1"></i>
                        <?= number_format($stats['keluarga_baru']) ?> KK baru bulan ini
                    </div>
                    <a class="small text-white" href="modules/keluarga/index.php">
                        <i class="fas fa-angle-right"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="small text-white-50">Total Peristiwa</div>
                            <div class="display-6"><?= number_format($stats['total_peristiwa']) ?></div>
                        </div>
                        <div class="fa-3x">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <div class="small text-white">
                        <i class="fas fa-plus me-1"></i>
                        <?= number_format($stats['peristiwa_baru']) ?> peristiwa baru bulan ini
                    </div>
                    <a class="small text-white" href="modules/peristiwa/index.php">
                        <i class="fas fa-angle-right"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="small text-white-50">Total Lokasi</div>
                            <div class="display-6"><?= number_format($stats['total_lokasi']) ?></div>
                        </div>
                        <div class="fa-3x">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <div class="small text-white">
                        <i class="fas fa-plus me-1"></i>
                        <?= number_format($stats['lokasi_baru']) ?> lokasi baru bulan ini
                    </div>
                    <a class="small text-white" href="modules/maps/index.php">
                        <i class="fas fa-angle-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistik Gender -->
    <div class="row mb-4">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-venus-mars me-1"></i>
                    Statistik Gender
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="mb-1">
                                <i class="fas fa-male fa-2x text-info"></i>
                            </div>
                            <h4 class="mb-0"><?= number_format($stats['total_laki']) ?></h4>
                            <div class="small text-muted">Laki-laki</div>
                            <div class="small">
                                <?= round(($stats['total_laki'] / max($stats['total_penduduk'], 1)) * 100) ?>% dari total
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-1">
                                <i class="fas fa-female fa-2x text-warning"></i>
                            </div>
                            <h4 class="mb-0"><?= number_format($stats['total_perempuan']) ?></h4>
                            <div class="small text-muted">Perempuan</div>
                            <div class="small">
                                <?= round(($stats['total_perempuan'] / max($stats['total_penduduk'], 1)) * 100) ?>% dari total
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-1"></i>
                    Statistik Peristiwa
                </div>
                <div class="card-body">
                    <canvas id="statistikPeristiwa"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Menu Utama -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="modules/penduduk/index.php" class="text-decoration-none">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Tabel Penduduk
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    Kelola Data Penduduk
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <a href="modules/keluarga/index.php" class="text-decoration-none">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Tabel Keluarga
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    Kelola Data Kartu Keluarga
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-home fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <a href="modules/peristiwa/index.php" class="text-decoration-none">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Tabel Peristiwa
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    Kelola Data Peristiwa
                                </div>
                                <div class="small text-muted mt-2">
                                    Kelahiran, Kematian, Pindah, Datang
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <a href="modules/maps/index.php" class="text-decoration-none">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Tabel Lokasi
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    Kelola Data Lokasi
                                </div>
                                <div class="small text-muted mt-2">
                                    <?= number_format($stats['total_lokasi']) ?> titik lokasi tercatat
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-map-marker-alt fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Menu Laporan -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-file-alt me-1"></i>
                    Menu Laporan
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <a href="modules/laporan/cetak.php" class="text-decoration-none">
                                <div class="card border-0 shadow-sm mb-3">
                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-print fa-2x text-primary"></i>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h5 class="card-title text-primary mb-1">Cetak Laporan</h5>
                                                <p class="card-text text-muted mb-0">
                                                    Cetak laporan data penduduk, keluarga, dan peristiwa
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="modules/laporan/statistik.php" class="text-decoration-none">
                                <div class="card border-0 shadow-sm mb-3">
                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-chart-bar fa-2x text-success"></i>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h5 class="card-title text-success mb-1">Statistik Data</h5>
                                                <p class="card-text text-muted mb-0">
                                                    Lihat statistik dan grafik data kependudukan
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
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
.border-left-primary {
    border-left: 4px solid #0d6efd !important;
}
.border-left-success {
    border-left: 4px solid #198754 !important;
}
.border-left-info {
    border-left: 4px solid #0dcaf0 !important;
}
.border-left-warning {
    border-left: 4px solid #ffc107 !important;
}
.text-xs {
    font-size: .7rem;
}
.card-footer {
    border-top: 1px solid rgba(255,255,255,0.15);
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Data untuk chart statistik peristiwa
    const ctx = document.getElementById('statistikPeristiwa');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Kelahiran', 'Kematian', 'Pindah', 'Datang'],
            datasets: [{
                label: 'Jumlah Peristiwa',
                data: [
                    <?= $stats['kelahiran'] ?>,
                    <?= $stats['kematian'] ?>,
                    <?= $stats['pindah'] ?>,
                    <?= $stats['datang'] ?>
                ],
                backgroundColor: [
                    'rgba(75, 192, 192, 0.5)',  // Kelahiran - Hijau
                    'rgba(255, 99, 132, 0.5)',   // Kematian - Merah
                    'rgba(255, 159, 64, 0.5)',   // Pindah - Orange
                    'rgba(54, 162, 235, 0.5)'    // Datang - Biru
                ],
                borderColor: [
                    'rgba(75, 192, 192, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(54, 162, 235, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>
