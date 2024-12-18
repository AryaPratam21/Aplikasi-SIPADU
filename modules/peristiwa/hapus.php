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

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $db = new Database();
    $conn = $db->getConnection();
    
    try {
        // Begin transaction
        $conn->beginTransaction();
        
        // Ambil data peristiwa sebelum dihapus untuk log
        $querySelect = "SELECT p.*, pd.nama, pd.nik 
                       FROM peristiwa p 
                       JOIN penduduk pd ON p.id_penduduk = pd.id_penduduk 
                       WHERE p.id_peristiwa = :id";
        $stmtSelect = $conn->prepare($querySelect);
        $stmtSelect->bindParam(':id', $id);
        $stmtSelect->execute();
        $peristiwa = $stmtSelect->fetch();
        
        if ($peristiwa) {
            // Hapus data peristiwa
            $queryDelete = "DELETE FROM peristiwa WHERE id_peristiwa = :id";
            $stmtDelete = $conn->prepare($queryDelete);
            $stmtDelete->bindParam(':id', $id);
            $stmtDelete->execute();
            
            // Catat log aktivitas
            $logQuery = "INSERT INTO log_aktivitas (user_id, aktivitas, keterangan) 
                        VALUES (:user_id, 'HAPUS', :keterangan)";
            $logStmt = $conn->prepare($logQuery);
            $logStmt->execute([
                ':user_id' => $_SESSION['user_id'],
                ':keterangan' => "Menghapus peristiwa {$peristiwa['jenis_peristiwa']} untuk penduduk: {$peristiwa['nama']} (NIK: {$peristiwa['nik']})"
            ]);
            
            // Commit transaction
            $conn->commit();
            
            $_SESSION['success'] = "Data peristiwa berhasil dihapus";
            
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
        } else {
            throw new Exception("Data peristiwa tidak ditemukan");
        }
    } catch(Exception $e) {
        // Rollback transaction jika terjadi error
        $conn->rollBack();
        error_log($e->getMessage());
        $_SESSION['error'] = "Terjadi kesalahan saat menghapus data";
    }
}

redirect('modules/peristiwa/index.php');
exit();