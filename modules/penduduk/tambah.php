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

$pageTitle = 'Tambah Penduduk';
$error = '';

// Inisialisasi database
$db = new Database();
$conn = $db->getConnection();

// Ambil data keluarga untuk dropdown
try {
    $queryKeluarga = "SELECT id_keluarga, no_kk, kepala_keluarga FROM keluarga ORDER BY no_kk ASC";
    $stmtKeluarga = $conn->query($queryKeluarga);
    $dataKeluarga = $stmtKeluarga->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log($e->getMessage());
    $_SESSION['error'] = "Terjadi kesalahan saat mengambil data keluarga";
}

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Validasi input
        $nik = cleanInput($_POST['nik']);
        $nama = cleanInput($_POST['nama']);
        $jenis_kelamin = cleanInput($_POST['jenis_kelamin']);
        $tanggal_lahir = cleanInput($_POST['tanggal_lahir']);
        $status_perkawinan = cleanInput($_POST['status_perkawinan']);
        $agama = cleanInput($_POST['agama']);
        $pekerjaan = cleanInput($_POST['pekerjaan']);
        $id_keluarga = (int)$_POST['id_keluarga'];
        $alamat = cleanInput($_POST['alamat']);
        
        // Validasi NIK
        if (strlen($nik) !== 16) {
            throw new Exception("NIK harus 16 digit");
        }
        
        // Cek NIK sudah ada atau belum
        $checkNik = "SELECT COUNT(*) FROM penduduk WHERE nik = ?";
        $stmtCheck = $conn->prepare($checkNik);
        $stmtCheck->execute([$nik]);
        if ($stmtCheck->fetchColumn() > 0) {
            throw new Exception("NIK sudah terdaftar");
        }
        
        // Insert data penduduk
        $query = "INSERT INTO penduduk (nik, nama, jenis_kelamin, tanggal_lahir, 
                                      status_perkawinan, agama, pekerjaan, id_keluarga, alamat) 
                 VALUES (:nik, :nama, :jenis_kelamin, :tanggal_lahir,
                         :status_perkawinan, :agama, :pekerjaan, :id_keluarga, :alamat)";
        
        $stmt = $conn->prepare($query);
        $stmt->execute([
            ':nik' => $nik,
            ':nama' => $nama,
            ':jenis_kelamin' => $jenis_kelamin,
            ':tanggal_lahir' => $tanggal_lahir,
            ':status_perkawinan' => $status_perkawinan,
            ':agama' => $agama,
            ':pekerjaan' => $pekerjaan,
            ':id_keluarga' => $id_keluarga,
            ':alamat' => $alamat
        ]);
        
        // Catat log aktivitas
        $logQuery = "INSERT INTO log_aktivitas (user_id, aktivitas, keterangan) 
                    VALUES (:user_id, 'TAMBAH', :keterangan)";
        $logStmt = $conn->prepare($logQuery);
        $logStmt->execute([
            ':user_id' => $_SESSION['user_id'],
            ':keterangan' => "Menambah data penduduk: $nama (NIK: $nik)"
        ]);
        
        $_SESSION['success'] = "Data penduduk berhasil ditambahkan";
        redirect('modules/penduduk/index.php');
        exit();
        
    } catch(Exception $e) {
        error_log($e->getMessage());
        $error = $e->getMessage();
    }
}

include '../../includes/header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4"><?= $pageTitle ?></h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="../../index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php">Penduduk</a></li>
        <li class="breadcrumb-item active">Tambah Penduduk</li>
    </ol>

    <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $error ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-user-plus me-1"></i>
            Form Tambah Penduduk
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="nik" class="form-label">NIK</label>
                            <input type="text" class="form-control" id="nik" name="nik" required 
                                maxlength="16" pattern="[0-9]{16}" title="NIK harus 16 digit angka">
                        </div>
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama" name="nama" required>
                        </div>
                        <div class="mb-3">
                            <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                            <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="tempat_lahir" class="form-label">Tempat Lahir</label>
                            <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                            <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" required>
                        </div>
                        <div class="mb-3">
                            <label for="agama" class="form-label">Agama</label>
                            <select class="form-select" id="agama" name="agama" required>
                                <option value="">Pilih Agama</option>
                                <option value="ISLAM">Islam</option>
                                <option value="KRISTEN">Kristen</option>
                                <option value="KATOLIK">Katolik</option>
                                <option value="HINDU">Hindu</option>
                                <option value="BUDDHA">Buddha</option>
                                <option value="KONGHUCU">Konghucu</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="status_perkawinan" class="form-label">Status Perkawinan</label>
                            <select class="form-select" id="status_perkawinan" name="status_perkawinan" required>
                                <option value="">Pilih Status</option>
                                <option value="BELUM MENIKAH">Belum Menikah</option>
                                <option value="MENIKAH">Menikah</option>
                                <option value="CERAI HIDUP">Cerai Hidup</option>
                                <option value="CERAI MATI">Cerai Mati</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="pekerjaan" class="form-label">Pekerjaan</label>
                            <input type="text" class="form-control" id="pekerjaan" name="pekerjaan" required>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="id_keluarga" class="form-label">Kartu Keluarga</label>
                    <select class="form-select" id="id_keluarga" name="id_keluarga" required>
                        <option value="">Pilih Kartu Keluarga</option>
                        <?php foreach($dataKeluarga as $keluarga): ?>
                        <option value="<?= $keluarga['id_keluarga'] ?>">
                            <?= $keluarga['no_kk'] ?> - <?= $keluarga['kepala_keluarga'] ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="alamat" class="form-label">Alamat</label>
                    <textarea class="form-control" id="alamat" name="alamat" required></textarea>
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
