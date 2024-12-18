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
    
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        // Validasi ID
        if ($id <= 0) {
            throw new Exception("ID penduduk tidak valid");
        }
        
        // Begin transaction
        $conn->beginTransaction();
        
        // Ambil data penduduk sebelum dihapus untuk log
        $querySelect = "SELECT nama, nik FROM penduduk WHERE id_penduduk = ? LIMIT 1";
        $penduduk = $db->fetchOne($querySelect, [$id]);
        
        if (!$penduduk) {
            throw new Exception("Data penduduk tidak ditemukan");
        }
        
        // Hapus data penduduk
        $queryDelete = "DELETE FROM penduduk WHERE id_penduduk = ?";
        $stmtDelete = $conn->prepare($queryDelete);
        
        if (!$stmtDelete->execute([$id])) {
            throw new Exception("Gagal menghapus data penduduk");
        }
        
        // Catat log aktivitas
        $logQuery = "INSERT INTO log_aktivitas (user_id, aktivitas, keterangan) 
                    VALUES (:user_id, 'HAPUS', :keterangan)";
        $logStmt = $conn->prepare($logQuery);
        
        if (!$logStmt->execute([
            ':user_id' => $_SESSION['user_id'],
            ':keterangan' => "Menghapus data penduduk: {$penduduk['nama']} (NIK: {$penduduk['nik']})"
        ])) {
            throw new Exception("Gagal mencatat log aktivitas");
        }
        
        // Commit transaction
        $conn->commit();
        $_SESSION['success'] = "Data penduduk berhasil dihapus";
        
    } catch(PDOException $e) {
        // Rollback transaction jika terjadi error
        if (isset($conn)) {
            $conn->rollBack();
        }
        error_log("Database Error: " . $e->getMessage());
        $_SESSION['error'] = "Terjadi kesalahan pada database";
    } catch(Exception $e) {
        // Rollback transaction jika terjadi error
        if (isset($conn)) {
            $conn->rollBack();
        }
        error_log("Error: " . $e->getMessage());
        $_SESSION['error'] = $e->getMessage();
    }
}

redirect('modules/penduduk/index.php');
exit();
