<?php
// Load required files with absolute paths
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/config.php';

// Prevent direct access
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Direct access not permitted');
}
?>
<!DOCTYPE html>
<html lang="id" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <!-- DateRangePicker -->
    <link href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
    <style>
        .navbar-brand {
            color: rgba(255, 255, 255, 0.9) !important;
        }
    </style>
</head>
<body class="d-flex flex-column h-100">

<?php if (isLoggedIn()): ?>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= BASE_URL ?>">
                <i class="fas fa-home me-2"></i><strong><?= APP_NAME ?></strong>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/modules/penduduk/">
                            <i class="fas fa-users me-1"></i>Data Penduduk
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/modules/keluarga/">
                            <i class="fas fa-home me-1"></i>Data Keluarga
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/modules/peristiwa/">
                            <i class="fas fa-calendar-alt me-1"></i>Data Peristiwa
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/modules/maps/">
                            <i class="fas fa-map-marker-alt me-1"></i>Data Lokasi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/modules/laporan/">
                            <i class="fas fa-file-alt me-1"></i>Laporan
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/modules/profil/">
                            <i class="fas fa-user me-1"></i><?= $_SESSION['nama_lengkap'] ?? 'Profil' ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/auth/logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i>Keluar
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
<?php endif; ?>

    <!-- Main Content -->
    <main class="flex-shrink-0">
    <div class="container mt-4">
