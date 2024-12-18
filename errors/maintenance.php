<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .maintenance-box {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .maintenance-icon {
            font-size: 64px;
            color: #f6c23e;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="maintenance-box">
            <i class="fas fa-tools maintenance-icon"></i>
            <h1 class="mb-4">Mode Pemeliharaan</h1>
            <p class="lead"><?= MAINTENANCE_MESSAGE ?></p>
            <p class="text-muted">Silakan kembali dalam beberapa saat.</p>
        </div>
    </div>
</body>
</html> 