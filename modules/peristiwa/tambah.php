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

$pageTitle = 'Tambah Peristiwa';
$error = '';

// Inisialisasi database
$db = new Database();
$conn = $db->getConnection();

// Ambil data penduduk untuk dropdown
try {
    $queryPenduduk = "SELECT p.id_penduduk, p.nik, p.nama 
                      FROM penduduk p
                      LEFT JOIN peristiwa pr ON p.id_penduduk = pr.id_penduduk
                      WHERE pr.id_penduduk IS NULL
                      ORDER BY p.nama ASC";
    $stmtPenduduk = $conn->query($queryPenduduk);
    $dataPenduduk = $stmtPenduduk->fetchAll();
} catch(PDOException $e) {
    error_log($e->getMessage());
    $_SESSION['error'] = "Terjadi kesalahan saat mengambil data penduduk";
}

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Validasi input
        $id_penduduk = (int)$_POST['id_penduduk'];
        $jenis_peristiwa = cleanInput($_POST['jenis_peristiwa']);
        $tanggal_peristiwa = cleanInput($_POST['tanggal_peristiwa']);
        $keterangan = cleanInput($_POST['keterangan']);
        
        // Insert data peristiwa
        $query = "INSERT INTO peristiwa (id_penduduk, jenis_peristiwa, tanggal_peristiwa, keterangan) 
                 VALUES (:id_penduduk, :jenis_peristiwa, :tanggal_peristiwa, :keterangan)";
        
        $stmt = $conn->prepare($query);
        $stmt->execute([
            ':id_penduduk' => $id_penduduk,
            ':jenis_peristiwa' => $jenis_peristiwa,
            ':tanggal_peristiwa' => $tanggal_peristiwa,
            ':keterangan' => $keterangan
        ]);
        
        $_SESSION['success'] = "Data peristiwa berhasil ditambahkan";
        header('Location: index.php');
        exit();
        
    } catch(Exception $e) {
        error_log($e->getMessage());
        $error = "Terjadi kesalahan saat menyimpan data";
    }
}

include '../../includes/header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4"><?= $pageTitle ?></h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="../../index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php">Peristiwa</a></li>
        <li class="breadcrumb-item active">Tambah Peristiwa</li>
    </ol>

    <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $error ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-plus me-1"></i>
            Form Tambah Peristiwa
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="id_penduduk" class="form-label">Penduduk</label>
                    <select class="form-select" id="id_penduduk" name="id_penduduk" required>
                        <option value="">Pilih Penduduk</option>
                        <?php foreach($dataPenduduk as $penduduk): ?>
                        <option value="<?= $penduduk['id_penduduk'] ?>">
                            <?= $penduduk['nik'] ?> - <?= $penduduk['nama'] ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="jenis_peristiwa" class="form-label">Jenis Peristiwa</label>
                    <select class="form-select" id="jenis_peristiwa" name="jenis_peristiwa" required>
                        <option value="">Pilih Jenis Peristiwa</option>
                        <option value="KELAHIRAN">Kelahiran</option>
                        <option value="KEMATIAN">Kematian</option>
                        <option value="PINDAH">Pindah</option>
                        <option value="DATANG">Datang</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="tanggal_peristiwa" class="form-label">Tanggal Peristiwa</label>
                    <input type="date" class="form-control" id="tanggal_peristiwa" 
                           name="tanggal_peristiwa" required>
                </div>
                <div class="mb-3">
                    <label for="keterangan" class="form-label">Keterangan</label>
                    <textarea class="form-control" id="keterangan" name="keterangan" 
                              rows="3" required></textarea>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="index.php" class="btn btn-secondary">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?> 