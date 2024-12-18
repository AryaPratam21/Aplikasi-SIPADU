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

$pageTitle = 'Edit Data Penduduk';
$error = '';
$penduduk = null;
$dataKeluarga = [];

// Inisialisasi database
try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Set error mode
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Ambil data keluarga untuk dropdown
    $queryKeluarga = "SELECT id_keluarga, no_kk, kepala_keluarga FROM keluarga ORDER BY no_kk ASC";
    $stmtKeluarga = $conn->prepare($queryKeluarga);
    $stmtKeluarga->execute();
    $dataKeluarga = $stmtKeluarga->fetchAll(PDO::FETCH_ASSOC);
    
    // Ambil data penduduk yang akan diedit
    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        
        try {
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
            
            // Gunakan method fetchOne
            $penduduk = $db->fetchOne($query, [$id]);
            
            if (!$penduduk) {
                throw new Exception("Data penduduk tidak ditemukan");
            }
            
            // Ambil data keluarga untuk dropdown
            $queryKeluarga = "SELECT id_keluarga, no_kk, kepala_keluarga 
                             FROM keluarga 
                             ORDER BY no_kk ASC";
            
            // Gunakan method fetchAll
            $dataKeluarga = $db->fetchAll($queryKeluarga);
            
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
    } else {
        $_SESSION['error'] = "ID penduduk tidak ditemukan";
        redirect('modules/penduduk/index.php');
        exit();
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

// Proses update data
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
        
        // Cek NIK duplikat kecuali untuk ID yang sedang diedit
        $checkNik = "SELECT COUNT(*) FROM penduduk WHERE nik = ? AND id_penduduk != ?";
        $stmtCheck = $conn->prepare($checkNik);
        $stmtCheck->execute([$nik, $id]);
        if ($stmtCheck->fetchColumn() > 0) {
            throw new Exception("NIK sudah terdaftar untuk penduduk lain");
        }
        
        // Update data penduduk
        $query = "UPDATE penduduk SET 
                  nik = :nik,
                  nama = :nama,
                  jenis_kelamin = :jenis_kelamin,
                  tanggal_lahir = :tanggal_lahir,
                  status_perkawinan = :status_perkawinan,
                  agama = :agama,
                  pekerjaan = :pekerjaan,
                  id_keluarga = :id_keluarga,
                  alamat = :alamat
                  WHERE id_penduduk = :id";
        
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
            ':alamat' => $alamat,
            ':id' => $id
        ]);
        
        // Catat log aktivitas
        $logQuery = "INSERT INTO log_aktivitas (user_id, aktivitas, keterangan) 
                    VALUES (:user_id, 'EDIT', :keterangan)";
        $logStmt = $conn->prepare($logQuery);
        $logStmt->execute([
            ':user_id' => $_SESSION['user_id'],
            ':keterangan' => "Mengubah data penduduk: $nama (NIK: $nik)"
        ]);
        
        $_SESSION['success'] = "Data penduduk berhasil diperbarui";
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
        <li class="breadcrumb-item active">Edit Penduduk</li>
    </ol>

    <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $error ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-user-edit me-1"></i>
            Form Edit Penduduk
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="nik" class="form-label">NIK</label>
                            <input type="text" class="form-control" id="nik" name="nik" required 
                                   maxlength="16" pattern="[0-9]{16}" title="NIK harus 16 digit angka"
                                   value="<?= htmlspecialchars($penduduk['nik']) ?>">
                        </div>
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama" name="nama" required
                                   value="<?= htmlspecialchars($penduduk['nama']) ?>">
                        </div>
                        <div class="mb-3">
                            <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                            <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="L" <?= $penduduk['jenis_kelamin'] == 'L' ? 'selected' : '' ?>>Laki-laki</option>
                                <option value="P" <?= $penduduk['jenis_kelamin'] == 'P' ? 'selected' : '' ?>>Perempuan</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                            <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" required
                                   value="<?= htmlspecialchars($penduduk['tanggal_lahir']) ?>">
                        </div>
                        <div class="mb-3">
                            <label for="agama" class="form-label">Agama</label>
                            <select class="form-select" id="agama" name="agama" required>
                                <option value="">Pilih Agama</option>
                                <?php
                                $agama_list = ['ISLAM', 'KRISTEN', 'KATOLIK', 'HINDU', 'BUDDHA', 'KONGHUCU'];
                                foreach($agama_list as $agama_option):
                                ?>
                                <option value="<?= $agama_option ?>" <?= $penduduk['agama'] == $agama_option ? 'selected' : '' ?>>
                                    <?= $agama_option ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="status_perkawinan" class="form-label">Status Perkawinan</label>
                            <select class="form-select" id="status_perkawinan" name="status_perkawinan" required>
                                <option value="">Pilih Status</option>
                                <?php
                                $status_list = ['BELUM MENIKAH', 'MENIKAH', 'CERAI HIDUP', 'CERAI MATI'];
                                foreach($status_list as $status_option):
                                ?>
                                <option value="<?= $status_option ?>" <?= $penduduk['status_perkawinan'] == $status_option ? 'selected' : '' ?>>
                                    <?= $status_option ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="pekerjaan" class="form-label">Pekerjaan</label>
                            <input type="text" class="form-control" id="pekerjaan" name="pekerjaan" required
                                   value="<?= htmlspecialchars($penduduk['pekerjaan']) ?>">
                        </div>
                        <div class="mb-3">
                            <label for="id_keluarga" class="form-label">Kartu Keluarga</label>
                            <select class="form-select" id="id_keluarga" name="id_keluarga" required>
                                <option value="">Pilih Kartu Keluarga</option>
                                <?php foreach($dataKeluarga as $keluarga): ?>
                                <option value="<?= $keluarga['id_keluarga'] ?>" 
                                        <?= $penduduk['id_keluarga'] == $keluarga['id_keluarga'] ? 'selected' : '' ?>>
                                    <?= $keluarga['no_kk'] ?> - <?= $keluarga['kepala_keluarga'] ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat</label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="3" required><?= htmlspecialchars($penduduk['alamat']) ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="index.php" class="btn btn-secondary">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
