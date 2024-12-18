<?php
http_response_code(404);
$page_title = '404 Not Found';
include '../includes/header.php';
?>

<div class="container">
    <div class="text-center mt-5">
        <h1 class="display-1">404</h1>
        <p class="lead">Halaman tidak ditemukan</p>
        <p>Maaf, halaman yang Anda cari tidak ditemukan.</p>
        <a href="<?= BASE_URL ?>" class="btn btn-primary">
            <i class="fas fa-home"></i> Kembali ke Dashboard
        </a>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 