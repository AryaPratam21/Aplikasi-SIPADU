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
        
        // Ambil data keluarga sebelum dihapus untuk log
        $querySelect = "SELECT no_kk, kepala_keluarga FROM keluarga WHERE id_keluarga = :id";
        $stmtSelect = $conn->prepare($querySelect);
        $stmtSelect->bindParam(':id', $id);
        $stmtSelect->execute();
        $keluarga = $stmtSelect->fetch();
        
        if ($keluarga) {
            // Hapus semua anggota keluarga
            $queryDeleteAnggota = "DELETE FROM penduduk WHERE id_keluarga = :id";
            $stmtDeleteAnggota = $conn->prepare($queryDeleteAnggota);
            $stmtDeleteAnggota->bindParam(':id', $id);
            $stmtDeleteAnggota->execute();
            
            // Hapus data keluarga
            $queryDelete = "DELETE FROM keluarga WHERE id_keluarga = :id";
            $stmtDelete = $conn->prepare($queryDelete);
            $stmtDelete->bindParam(':id', $id);
            $stmtDelete->execute();
            
            // Catat log aktivitas
            $logQuery = "INSERT INTO log_aktivitas (user_id, aktivitas, keterangan) 
                        VALUES (:user_id, 'HAPUS', :keterangan)";
            $logStmt = $conn->prepare($logQuery);
            $logStmt->execute([
                ':user_id' => $_SESSION['user_id'],
                ':keterangan' => "Menghapus kartu keluarga: {$keluarga['no_kk']} ({$keluarga['kepala_keluarga']})"
            ]);
            
            // Commit transaction
            $conn->commit();
            
            $_SESSION['success'] = "Data kartu keluarga berhasil dihapus";
        } else {
            throw new Exception("Data kartu keluarga tidak ditemukan");
        }
    } catch(Exception $e) {
        // Rollback transaction jika terjadi error
        $conn->rollBack();
        error_log($e->getMessage());
        $_SESSION['error'] = "Terjadi kesalahan saat menghapus data";
    }
}

redirect('modules/keluarga/index.php');
exit();