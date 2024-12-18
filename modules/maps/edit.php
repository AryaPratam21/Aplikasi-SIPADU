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

$pageTitle = 'Edit Lokasi Penduduk';

// Inisialisasi database
$db = new Database();
$conn = $db->getConnection();

// Cek ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "ID tidak valid";
    redirect('index.php');
    exit();
}

$id_maps = (int)$_GET['id'];

// Ambil data maps dan penduduk
try {
    $query = "SELECT m.*, p.nik, p.nama 
              FROM maps m 
              JOIN penduduk p ON m.id_penduduk = p.id_penduduk 
              WHERE m.id_maps = :id_maps";
    $stmt = $conn->prepare($query);
    $stmt->execute(['id_maps' => $id_maps]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        $_SESSION['error'] = "Data tidak ditemukan";
        redirect('index.php');
        exit();
    }

} catch(PDOException $e) {
    error_log($e->getMessage());
    $_SESSION['error'] = "Gagal mengambil data";
    redirect('index.php');
    exit();
}

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $latitude = cleanInput($_POST['latitude']);
    $longitude = cleanInput($_POST['longitude']);
    $errors = [];

    // Validasi
    if (empty($latitude)) {
        $errors[] = "Latitude harus diisi";
    }
    if (empty($longitude)) {
        $errors[] = "Longitude harus diisi";
    }

    // Validasi format koordinat
    if (!empty($latitude) && (!is_numeric($latitude) || $latitude < -90 || $latitude > 90)) {
        $errors[] = "Format latitude tidak valid (harus antara -90 sampai 90)";
    }
    if (!empty($longitude) && (!is_numeric($longitude) || $longitude < -180 || $longitude > 180)) {
        $errors[] = "Format longitude tidak valid (harus antara -180 sampai 180)";
    }

    // Jika tidak ada error, update data
    if (empty($errors)) {
        try {
            $query = "UPDATE maps 
                     SET latitude = :latitude, 
                         longitude = :longitude 
                     WHERE id_maps = :id_maps";
            $stmt = $conn->prepare($query);
            $stmt->execute([
                'latitude' => $latitude,
                'longitude' => $longitude,
                'id_maps' => $id_maps
            ]);

            $_SESSION['success'] = "Data lokasi berhasil diupdate";
            header('Location: index.php');
            exit();

        } catch(PDOException $e) {
            error_log($e->getMessage());
            $errors[] = "Gagal mengupdate data lokasi";
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
        <li class="breadcrumb-item active">Edit Lokasi</li>
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

    <div class="row">
        <div class="col-xl-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-edit me-1"></i>
                    Form Edit Lokasi
                </div>
                <div class="card-body">
                    <form action="" method="POST">
                        <div class="mb-3">
                            <label class="form-label">NIK</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($data['nik']) ?>" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($data['nama']) ?>" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="latitude" class="form-label">Latitude</label>
                            <input type="text" class="form-control" id="latitude" name="latitude" 
                                   value="<?= htmlspecialchars($data['latitude']) ?>" required>
                            <div class="form-text">Contoh format: -6.5888</div>
                        </div>
                        <div class="mb-3">
                            <label for="longitude" class="form-label">Longitude</label>
                            <input type="text" class="form-control" id="longitude" name="longitude" 
                                   value="<?= htmlspecialchars($data['longitude']) ?>" required>
                            <div class="form-text">Contoh format: 110.6684</div>
                        </div>
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Simpan
                            </button>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
