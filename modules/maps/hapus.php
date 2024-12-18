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

// Cek ID
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "ID tidak valid";
    header('Location: index.php');
    exit();
}

$id_maps = (int)$_GET['id'];

// Inisialisasi database
$db = new Database();
$conn = $db->getConnection();

try {
    // Cek apakah data ada
    $query = "SELECT id_maps FROM maps WHERE id_maps = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$id_maps]);
    
    if ($stmt->rowCount() == 0) {
        $_SESSION['error'] = "Data tidak ditemukan";
        header('Location: index.php');
        exit();
    }

    // Hapus data
    $query = "DELETE FROM maps WHERE id_maps = ?";
    $stmt = $conn->prepare($query);
    $result = $stmt->execute([$id_maps]);

    if ($result) {
        $_SESSION['success'] = "Data lokasi berhasil dihapus";
    } else {
        $_SESSION['error'] = "Gagal menghapus data";
    }

} catch(PDOException $e) {
    error_log($e->getMessage());
    $_SESSION['error'] = "Gagal menghapus data: " . $e->getMessage();
}

header('Location: index.php');
exit();
