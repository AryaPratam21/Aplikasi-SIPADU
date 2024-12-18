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

$pageTitle = 'Statistik Data';
$db = (new Database())->getConnection();

// Inisialisasi variabel
$stats = [
    'total_penduduk' => 0,
    'total_keluarga' => 0,
    'total_laki' => 0,
    'total_perempuan' => 0,
    'monthly_data' => [],
    'monthly_peristiwa' => []
];

try {
    // Query untuk total penduduk
    $query = "SELECT COUNT(*) as total FROM penduduk";
    $stmt = $db->query($query);
    if ($stmt) {
        $stats['total_penduduk'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // Query untuk total keluarga
    $query = "SELECT COUNT(*) as total FROM keluarga";
    $stmt = $db->query($query);
    if ($stmt) {
        $stats['total_keluarga'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // Query untuk total penduduk laki-laki
    $query = "SELECT COUNT(*) as total FROM penduduk WHERE jenis_kelamin = 'L'";
    $stmt = $db->query($query);
    if ($stmt) {
        $stats['total_laki'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // Query untuk total penduduk perempuan
    $query = "SELECT COUNT(*) as total FROM penduduk WHERE jenis_kelamin = 'P'";
    $stmt = $db->query($query);
    if ($stmt) {
        $stats['total_perempuan'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // Query untuk data bulanan (12 bulan terakhir)
    $query = "SELECT 
              DATE_FORMAT(created_at, '%Y-%m') as bulan,
              COUNT(*) as total_penduduk,
              SUM(CASE WHEN jenis_kelamin = 'L' THEN 1 ELSE 0 END) as total_laki,
              SUM(CASE WHEN jenis_kelamin = 'P' THEN 1 ELSE 0 END) as total_perempuan
              FROM penduduk
              WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
              GROUP BY DATE_FORMAT(created_at, '%Y-%m')
              ORDER BY bulan DESC";
    
    $stmt = $db->query($query);
    if ($stmt) {
        $stats['monthly_data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Query untuk data peristiwa bulanan
    $query = "SELECT 
              DATE_FORMAT(tanggal_peristiwa, '%Y-%m') as bulan,
              COUNT(*) as total_peristiwa,
              SUM(CASE WHEN jenis_peristiwa = 'KELAHIRAN' THEN 1 ELSE 0 END) as kelahiran,
              SUM(CASE WHEN jenis_peristiwa = 'KEMATIAN' THEN 1 ELSE 0 END) as kematian,
              SUM(CASE WHEN jenis_peristiwa = 'PINDAH' THEN 1 ELSE 0 END) as pindah,
              SUM(CASE WHEN jenis_peristiwa = 'DATANG' THEN 1 ELSE 0 END) as datang
              FROM peristiwa
              WHERE tanggal_peristiwa >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
              GROUP BY DATE_FORMAT(tanggal_peristiwa, '%Y-%m')
              ORDER BY bulan DESC";
    
    $stmt = $db->query($query);
    if ($stmt) {
        $stats['monthly_peristiwa'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

} catch(PDOException $e) {
    error_log($e->getMessage());
    $error = "Terjadi kesalahan saat mengambil data statistik.";
}

include '../../includes/header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4"><?= $pageTitle ?></h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="../../index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php">Laporan</a></li>
        <li class="breadcrumb-item active">Statistik Data</li>
    </ol>

    <?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <?= $error ?>
    </div>
    <?php endif; ?>

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
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="small text-white-50">Laki-laki</div>
                            <div class="display-6"><?= number_format($stats['total_laki']) ?></div>
                        </div>
                        <div class="fa-3x">
                            <i class="fas fa-male"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="small text-white-50">Perempuan</div>
                            <div class="display-6"><?= number_format($stats['total_perempuan']) ?></div>
                        </div>
                        <div class="fa-3x">
                            <i class="fas fa-female"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-chart-bar me-1"></i>
                        Data Bulanan (12 Bulan Terakhir)
                    </div>
                    <div>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Bulan</th>
                                    <th class="text-center">Total Penduduk</th>
                                    <th class="text-center">Laki-laki</th>
                                    <th class="text-center">Perempuan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $months = [
                                    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
                                    '04' => 'April', '05' => 'Mei', '06' => 'Juni',
                                    '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
                                    '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                                ];
                                
                                foreach($stats['monthly_data'] as $row): 
                                    list($year, $month) = explode('-', $row['bulan']);
                                ?>
                                <tr>
                                    <td><?= $months[$month] ?> <?= $year ?></td>
                                    <td class="text-center"><?= number_format($row['total_penduduk']) ?></td>
                                    <td class="text-center"><?= number_format($row['total_laki']) ?></td>
                                    <td class="text-center"><?= number_format($row['total_perempuan']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($stats['monthly_data'])): ?>
                                <tr>
                                    <td colspan="4" class="text-center">Tidak ada data untuk ditampilkan</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-chart-bar me-1"></i>
                        Data Peristiwa Bulanan (12 Bulan Terakhir)
                    </div>
                    <div>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Bulan</th>
                                    <th class="text-center">Total Peristiwa</th>
                                    <th class="text-center">Kelahiran</th>
                                    <th class="text-center">Kematian</th>
                                    <th class="text-center">Pindah</th>
                                    <th class="text-center">Datang</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $months = [
                                    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
                                    '04' => 'April', '05' => 'Mei', '06' => 'Juni',
                                    '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
                                    '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                                ];
                                
                                foreach($stats['monthly_peristiwa'] as $row): 
                                    list($year, $month) = explode('-', $row['bulan']);
                                ?>
                                <tr>
                                    <td><?= $months[$month] ?> <?= $year ?></td>
                                    <td class="text-center"><?= number_format($row['total_peristiwa']) ?></td>
                                    <td class="text-center"><?= number_format($row['kelahiran']) ?></td>
                                    <td class="text-center"><?= number_format($row['kematian']) ?></td>
                                    <td class="text-center"><?= number_format($row['pindah']) ?></td>
                                    <td class="text-center"><?= number_format($row['datang']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($stats['monthly_peristiwa'])): ?>
                                <tr>
                                    <td colspan="6" class="text-center">Tidak ada data untuk ditampilkan</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>