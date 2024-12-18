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

$pageTitle = 'Edit Peristiwa';
$error = '';
$peristiwa = null;

// Inisialisasi database
$db = new Database();
$conn = $db->getConnection();

// Ambil data penduduk untuk dropdown
try {
    $queryPenduduk = "SELECT id_penduduk, nik, nama FROM penduduk ORDER BY nama ASC";
    $stmtPenduduk = $conn->query($queryPenduduk);
    $dataPenduduk = $stmtPenduduk->fetchAll();
} catch(PDOException $e) {
    error_log($e->getMessage());
    $_SESSION['error'] = "Terjadi kesalahan saat mengambil data penduduk";
}

// Ambil data peristiwa yang akan diedit
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    try {
        $query = "SELECT * FROM peristiwa WHERE id_peristiwa = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $peristiwa = $stmt->fetch();
        } else {
            $_SESSION['error'] = "Data peristiwa tidak ditemukan";
            redirect('modules/peristiwa/index.php');
            exit();
        }
    } catch(PDOException $e) {
        error_log($e->getMessage());
        $_SESSION['error'] = "Terjadi kesalahan saat mengambil data";
        redirect('modules/peristiwa/index.php');
        exit();
    }
}

// Proses update data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Validasi input
        $id_penduduk = (int)$_POST['id_penduduk'];
        $jenis_peristiwa = cleanInput($_POST['jenis_peristiwa']);
        $tanggal_peristiwa = cleanInput($_POST['tanggal_peristiwa']);
        $keterangan = cleanInput($_POST['keterangan']);
        
        // Update data peristiwa
        $query = "UPDATE peristiwa SET 
                  id_penduduk = :id_penduduk,
                  jenis_peristiwa = :jenis_peristiwa,
                  tanggal_peristiwa = :tanggal_peristiwa,
                  keterangan = :keterangan
                  WHERE id_peristiwa = :id";
        
        $stmt = $conn->prepare($query);
        $stmt->execute([
            ':id_penduduk' => $id_penduduk,
            ':jenis_peristiwa' => $jenis_peristiwa,
            ':tanggal_peristiwa' => $tanggal_peristiwa,
            ':keterangan' => $keterangan,
            ':id' => $id
        ]);
        
        $_SESSION['success'] = "Data peristiwa berhasil diperbarui";
        
        // Tambahkan script untuk refresh dashboard
        echo "<script>
            if (window.opener && !window.opener.closed) {
                if (window.opener.location.pathname.endsWith('index.php')) {
                    window.opener.location.reload();
                }
            }
            window.location.href = 'index.php';
        </script>";
        exit();
        
    } catch(Exception $e) {
        error_log($e->getMessage());
        $error = "Terjadi kesalahan saat memperbarui data";
    }
}

include '../../includes/header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4"><?= $pageTitle ?></h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="../../index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php">Peristiwa</a></li>
        <li class="breadcrumb-item active">Edit Peristiwa</li>
    </ol>

    <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $error ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-edit me-1"></i>
            Form Edit Peristiwa
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="id_penduduk" class="form-label">Penduduk</label>
                    <select class="form-select" id="id_penduduk" name="id_penduduk" required>
                        <option value="">Pilih Penduduk</option>
                        <?php foreach($dataPenduduk as $penduduk): ?>
                        <option value="<?= $penduduk['id_penduduk'] ?>" 
                                <?= $peristiwa['id_penduduk'] == $penduduk['id_penduduk'] ? 'selected' : '' ?>>
                            <?= $penduduk['nik'] ?> - <?= $penduduk['nama'] ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="jenis_peristiwa" class="form-label">Jenis Peristiwa</label>
                    <select class="form-select" id="jenis_peristiwa" name="jenis_peristiwa" required>
                        <option value="">Pilih Jenis Peristiwa</option>
                        <?php
                        $jenis_list = ['KELAHIRAN', 'KEMATIAN', 'PINDAH', 'DATANG'];
                        foreach($jenis_list as $jenis):
                        ?>
                        <option value="<?= $jenis ?>" 
                                <?= $peristiwa['jenis_peristiwa'] == $jenis ? 'selected' : '' ?>>
                            <?= $jenis ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="tanggal_peristiwa" class="form-label">Tanggal Peristiwa</label>
                    <input type="date" class="form-control" id="tanggal_peristiwa" 
                           name="tanggal_peristiwa" required
                           value="<?= htmlspecialchars($peristiwa['tanggal_peristiwa']) ?>">
                </div>
                <div class="mb-3">
                    <label for="keterangan" class="form-label">Keterangan</label>
                    <textarea class="form-control" id="keterangan" name="keterangan" 
                              rows="3" required><?= htmlspecialchars($peristiwa['keterangan']) ?></textarea>
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