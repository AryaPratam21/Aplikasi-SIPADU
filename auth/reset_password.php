<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('/auth/login.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    try {
        $db = (new Database())->getConnection();
        
        // Validate input
        if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
            throw new Exception('Semua field harus diisi');
        }
        
        if ($new_password !== $confirm_password) {
            throw new Exception('Password baru tidak cocok');
        }
        
        if (strlen($new_password) < 6) {
            throw new Exception('Password minimal 6 karakter');
        }
        
        // Get current user data
        $query = "SELECT password FROM users WHERE id = ? LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if (!verifyPassword($old_password, $user['password'])) {
            throw new Exception('Password lama salah');
        }
        
        // Update password
        $new_hash = generatePasswordHash($new_password);
        $updateQuery = "UPDATE users SET password = ? WHERE id = ?";
        $stmt = $db->prepare($updateQuery);
        $stmt->execute([$new_hash, $_SESSION['user_id']]);
        
        // Log activity
        logActivity($db, 'Password berhasil diubah', 'auth');
        
        $success = 'Password berhasil diubah';
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$page_title = 'Reset Password';
include '../includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0">Reset Password</h5>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?= $success ?>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Password Lama</label>
                            <input type="password" name="old_password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password Baru</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-key"></i> Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 