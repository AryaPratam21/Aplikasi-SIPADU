<?php
http_response_code(403);
$page_title = '403 Forbidden';
include '../includes/header.php';
?>

<div class="container">
    <div class="text-center mt-5">
        <h1 class="display-1">403</h1>
        <p class="lead">Akses Terlarang</p>
        <p>Maaf, Anda tidak memiliki akses ke halaman ini.</p>
        <a href="<?= BASE_URL ?>" class="btn btn-primary">
            <i class="fas fa-home"></i> Kembali ke Dashboard
        </a>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 