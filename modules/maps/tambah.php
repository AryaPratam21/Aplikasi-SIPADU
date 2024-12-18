<?php
require_once '../../config/constants.php';
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Cek login
if (!isLoggedIn()) {
    redirect('../../auth/login.php');
    exit();
}

$pageTitle = 'Tambah Lokasi Penduduk';

// Inisialisasi database
$db = new Database();
$conn = $db->getConnection();

// Ambil data penduduk untuk dropdown
try {
    $queryPenduduk = "SELECT p.id_penduduk, p.nik, p.nama 
                      FROM penduduk p 
                      LEFT JOIN maps m ON p.id_penduduk = m.id_penduduk 
                      WHERE m.id_maps IS NULL 
                      ORDER BY p.nama ASC";
    $stmtPenduduk = $conn->query($queryPenduduk);
    $dataPenduduk = $stmtPenduduk->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log($e->getMessage());
    $_SESSION['error'] = "Gagal mengambil data penduduk";
    redirect('index.php');
    exit();
}

// Jika tidak ada penduduk yang bisa ditambahkan lokasinya
if (empty($dataPenduduk)) {
    $_SESSION['warning'] = "Tidak ada penduduk yang bisa ditambahkan lokasinya (semua penduduk sudah memiliki lokasi)";
    redirect('index.php');
    exit();
}

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_penduduk = cleanInput($_POST['id_penduduk']);
    $latitude = cleanInput($_POST['latitude']);
    $longitude = cleanInput($_POST['longitude']);
    $errors = [];

    // Validasi input
    if (empty($id_penduduk)) {
        $errors[] = "Penduduk harus dipilih";
    }
    if (!is_numeric($latitude) || $latitude < -90 || $latitude > 90) {
        $errors[] = "Latitude harus berupa angka dan berada dalam rentang -90 sampai 90";
    }
    if (!is_numeric($longitude) || $longitude < -180 || $longitude > 180) {
        $errors[] = "Longitude harus berupa angka dan berada dalam rentang -180 sampai 180";
    }

    // Simpan data jika tidak ada error
    if (empty($errors)) {
        try {
            $query = "INSERT INTO maps (id_penduduk, latitude, longitude) VALUES (:id_penduduk, :latitude, :longitude)";
            $stmt = $conn->prepare($query);
            $stmt->execute([
                'id_penduduk' => $id_penduduk,
                'latitude' => $latitude,
                'longitude' => $longitude
            ]);

            $_SESSION['success'] = "Data lokasi berhasil ditambahkan";
            header('Location: index.php');
            exit();

        } catch(PDOException $e) {
            error_log($e->getMessage());
            $errors[] = "Gagal menyimpan data lokasi";
        }
    }
}

include '../../includes/header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4"><?= $pageTitle ?></h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="../../index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php">Data Lokasi</a></li>
        <li class="breadcrumb-item active">Tambah Lokasi</li>
    </ol>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
            <li><?= $error ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-map-marker-alt me-1"></i> Form Tambah Lokasi
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label for="id_penduduk" class="form-label">Pilih Penduduk</label>
                    <select class="form-select" id="id_penduduk" name="id_penduduk" required>
                        <option value="">Pilih Penduduk...</option>
                        <?php foreach ($dataPenduduk as $penduduk): ?>
                        <option value="<?= $penduduk['id_penduduk'] ?>">
                            <?= $penduduk['nik'] ?> - <?= $penduduk['nama'] ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="latitude" class="form-label">Latitude</label>
                    <input type="number" step="any" class="form-control" id="latitude" name="latitude" required value="<?= isset($_POST['latitude']) ? htmlspecialchars($_POST['latitude']) : '' ?>">
                    <div class="form-text">Contoh: -6.200000</div>
                </div>
                <div class="mb-3">
                    <label for="longitude" class="form-label">Longitude</label>
                    <input type="number" step="any" class="form-control" id="longitude" name="longitude" required value="<?= isset($_POST['longitude']) ? htmlspecialchars($_POST['longitude']) : '' ?>">
                    <div class="form-text">Contoh: 106.816666</div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Simpan
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Kembali
                </a>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
