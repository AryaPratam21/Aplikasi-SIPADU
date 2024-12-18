<?php
http_response_code(500);
$page_title = '500 Server Error';
include '../includes/header.php';
?>

<div class="container">
    <div class="text-center mt-5">
        <h1 class="display-1">500</h1>
        <p class="lead">Internal Server Error</p>
        <p>Maaf, terjadi kesalahan pada server. Silakan coba beberapa saat lagi.</p>
        <a href="<?= BASE_URL ?>" class="btn btn-primary">
            <i class="fas fa-home"></i> Kembali ke Dashboard
        </a>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 