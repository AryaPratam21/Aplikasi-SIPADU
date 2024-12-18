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

$pageTitle = 'Cetak Laporan';
$db = (new Database())->getConnection();

// Filter
$jenis = isset($_GET['jenis']) ? cleanInput($_GET['jenis']) : '';
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');
$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : date('m');

// Fungsi untuk mendapatkan data berdasarkan jenis laporan
function getData($db, $jenis, $tahun, $bulan) {
    $data = [];
    $params = [':tahun' => $tahun];
    
    switch($jenis) {
        case 'penduduk':
            $query = "SELECT p.*, k.no_kk, k.kepala_keluarga as nama_kepala 
                     FROM penduduk p 
                     LEFT JOIN keluarga k ON p.id_keluarga = k.id_keluarga 
                     WHERE YEAR(p.created_at) = :tahun";
            if ($bulan) {
                $query .= " AND MONTH(p.created_at) = :bulan";
                $params[':bulan'] = $bulan;
            }
            $query .= " ORDER BY p.created_at DESC";
            break;
            
        case 'keluarga':
            $query = "SELECT k.*, 
                     (SELECT COUNT(*) FROM penduduk p WHERE p.id_keluarga = k.id_keluarga) as jumlah_anggota
                     FROM keluarga k 
                     WHERE YEAR(k.created_at) = :tahun";
            if ($bulan) {
                $query .= " AND MONTH(k.created_at) = :bulan";
                $params[':bulan'] = $bulan;
            }
            $query .= " ORDER BY k.created_at DESC";
            break;
            
        case 'lokasi':
            $query = "SELECT m.*, p.nik, p.nama 
                     FROM maps m 
                     JOIN penduduk p ON m.id_penduduk = p.id_penduduk 
                     WHERE YEAR(m.created_at) = :tahun";
            if ($bulan) {
                $query .= " AND MONTH(m.created_at) = :bulan";
                $params[':bulan'] = $bulan;
            }
            $query .= " ORDER BY m.created_at DESC";
            break;
            
        case 'peristiwa':
            $query = "SELECT p.jenis_peristiwa as jenis,
                            p.tanggal_peristiwa,
                            pd.nama,
                            p.keterangan,
                            p.created_at
                     FROM peristiwa p
                     JOIN penduduk pd ON p.id_penduduk = pd.id_penduduk
                     WHERE YEAR(p.tanggal_peristiwa) = :tahun";
            if ($bulan) {
                $query .= " AND MONTH(p.tanggal_peristiwa) = :bulan";
                $params[':bulan'] = $bulan;
            }
            $query .= " ORDER BY p.tanggal_peristiwa ASC";
            break;
            
        default:
            return [];
    }
    
    try {
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log($e->getMessage());
    }
    
    return $data;
}

// Jika form disubmit
$data = [];
if ($_SERVER['REQUEST_METHOD'] == 'GET' && $jenis) {
    $data = getData($db, $jenis, $tahun, $bulan);
}

include '../../includes/header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4"><?= $pageTitle ?></h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="../../index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Cetak Laporan</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-file-alt me-1"></i>
            Form Cetak Laporan
        </div>
        <div class="card-body">
            <form method="GET" class="mb-3">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Jenis Laporan</label>
                            <select name="jenis" class="form-select" required>
                                <option value="">Pilih Jenis Laporan...</option>
                                <option value="penduduk" <?= $jenis == 'penduduk' ? 'selected' : '' ?>>Data Penduduk</option>
                                <option value="keluarga" <?= $jenis == 'keluarga' ? 'selected' : '' ?>>Data Kartu Keluarga</option>
                                <option value="lokasi" <?= $jenis == 'lokasi' ? 'selected' : '' ?>>Data Lokasi</option>
                                <option value="peristiwa" <?= $jenis == 'peristiwa' ? 'selected' : '' ?>>Data Peristiwa</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Tahun</label>
                            <select name="tahun" class="form-select">
                                <?php 
                                $currentYear = (int)date('Y');
                                for($y = $currentYear; $y >= $currentYear - 5; $y--): 
                                ?>
                                <option value="<?= $y ?>" <?= $tahun == $y ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Bulan</label>
                            <select name="bulan" class="form-select">
                                <option value="">Semua Bulan</option>
                                <?php 
                                $months = [
                                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
                                    4 => 'April', 5 => 'Mei', 6 => 'Juni',
                                    7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                                    10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                ];
                                foreach($months as $num => $name): 
                                ?>
                                <option value="<?= $num ?>" <?= $bulan == $num ? 'selected' : '' ?>><?= $name ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-1"></i>Tampilkan
                                </button>
                                <?php if (!empty($data)): ?>
                                <button type="button" class="btn btn-success" onclick="window.print()">
                                    <i class="fas fa-print me-1"></i>Cetak
                                </button>
                                <?php endif; ?>
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-1"></i>Kembali
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <?php if (!empty($data)): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <?php if ($jenis == 'penduduk'): ?>
                            <th>NIK</th>
                            <th>Nama</th>
                            <th>Jenis Kelamin</th>
                            <th>Tanggal Lahir</th>
                            <th>Alamat</th>
                            <th>No. KK</th>
                            <th>Kepala Keluarga</th>
                            <?php elseif ($jenis == 'keluarga'): ?>
                            <th>No. KK</th>
                            <th>Kepala Keluarga</th>
                            <th>Alamat</th>
                            <th>Jumlah Anggota</th>
                            <th>Tanggal Input</th>
                            <?php elseif ($jenis == 'lokasi'): ?>
                            <th>NIK</th>
                            <th>Nama</th>
                            <th>Latitude</th>
                            <th>Longitude</th>
                            <th>Tanggal Input</th>
                            <?php elseif ($jenis == 'peristiwa'): ?>
                            <th>Jenis Peristiwa</th>
                            <th>Tanggal Peristiwa</th>
                            <th>Nama</th>
                            <th>Keterangan</th>
                            <th>Tanggal Input</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        foreach($data as $row): 
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <?php if ($jenis == 'penduduk'): ?>
                            <td><?= htmlspecialchars($row['nik']) ?></td>
                            <td><?= htmlspecialchars($row['nama']) ?></td>
                            <td><?= $row['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan' ?></td>
                            <td><?= date('d/m/Y', strtotime($row['tanggal_lahir'])) ?></td>
                            <td><?= htmlspecialchars($row['alamat']) ?></td>
                            <td><?= htmlspecialchars($row['no_kk'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($row['kepala_keluarga'] ?? '-') ?></td>
                            <?php elseif ($jenis == 'keluarga'): ?>
                            <td><?= htmlspecialchars($row['no_kk']) ?></td>
                            <td><?= htmlspecialchars($row['kepala_keluarga']) ?></td>
                            <td><?= htmlspecialchars($row['alamat']) ?></td>
                            <td><?= number_format($row['jumlah_anggota']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                            <?php elseif ($jenis == 'lokasi'): ?>
                            <td><?= htmlspecialchars($row['nik']) ?></td>
                            <td><?= htmlspecialchars($row['nama']) ?></td>
                            <td><?= $row['latitude'] ?></td>
                            <td><?= $row['longitude'] ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                            <?php elseif ($jenis == 'peristiwa'): ?>
                            <td><?= htmlspecialchars($row['jenis']) ?></td>
                            <td><?= date('d/m/Y', strtotime($row['tanggal_peristiwa'])) ?></td>
                            <td><?= htmlspecialchars($row['nama']) ?></td>
                            <td><?= htmlspecialchars($row['keterangan']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php elseif ($_SERVER['REQUEST_METHOD'] == 'GET' && $jenis): ?>
            <div class="alert alert-info">
                Tidak ada data untuk periode yang dipilih.
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
@media print {
    .navbar, .sidebar, .breadcrumb, form, .btn, footer {
        display: none !important;
    }
    .card {
        border: none !important;
    }
    .card-header {
        background: none !important;
        border: none !important;
    }
    @page {
        margin: 0.5cm;
    }
}
</style>

<?php include '../../includes/footer.php'; ?>