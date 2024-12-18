<?php
// Load configurations first
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Debug mode
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set page title
$pageTitle = 'Login - SIPANDU';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('index.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'register') {
        $username = cleanInput($_POST['username']);
        $password = cleanInput($_POST['password']);
        $confirm_password = cleanInput($_POST['confirm_password']);
        $nama_lengkap = cleanInput($_POST['nama_lengkap']);
        
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            // Validate input
            if (empty($username) || empty($password) || empty($confirm_password) || empty($nama_lengkap)) {
                $error = 'Semua field harus diisi';
            } elseif ($password !== $confirm_password) {
                $error = 'Password tidak cocok';
            } else {
                // Check if username already exists
                $stmt = $conn->prepare("SELECT id_user FROM users WHERE username = ?");
                $stmt->execute([$username]);
                if ($stmt->fetch()) {
                    $error = 'Username sudah digunakan';
                } else {
                    // Hash password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insert new user
                    $stmt = $conn->prepare("INSERT INTO users (username, password, nama_lengkap) VALUES (?, ?, ?)");
                    if ($stmt->execute([$username, $hashed_password, $nama_lengkap])) {
                        $success = 'Registrasi berhasil! Silakan login.';
                    } else {
                        $error = 'Gagal mendaftarkan user';
                    }
                }
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            $error = 'Terjadi kesalahan sistem';
        }
    } else {
        // Login process
        $username = cleanInput($_POST['username']);
        $password = cleanInput($_POST['password']);
        
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            if (verifyLogin($username, $password, $conn)) {
                redirect('index.php');
                exit();
            } else {
                $error = 'Username atau password salah';
                error_log("Login failed for user: $username");
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            $error = 'Terjadi kesalahan sistem';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5">
                <div class="card shadow-lg border-0 rounded-lg mt-5">
                    <div class="card-header bg-primary text-white text-center py-4">
                        <h2 class="fs-4 fw-bold"><?= APP_NAME ?></h2>
                        <p class="mb-0">Sistem Informasi Pendataan Penduduk</p>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i><?= $success ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Login Form -->
                        <form method="POST" action="<?= $_SERVER['PHP_SELF'] ?>" class="needs-validation" novalidate id="loginForm">
                            <div class="mb-3">
                                <label class="form-label" for="username">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input class="form-control" id="username" name="username" type="text" 
                                           placeholder="Masukkan username" required 
                                           value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" />
                                    <div class="invalid-feedback">Username harus diisi</div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label" for="password">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input class="form-control" id="password" name="password" type="password" 
                                           placeholder="Masukkan password" required />
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <div class="invalid-feedback">Password harus diisi</div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                                <button type="button" class="btn btn-link text-decoration-none" onclick="toggleForms()">Belum punya akun? Daftar</button>
                                <button class="btn btn-primary" type="submit">Login</button>
                            </div>
                        </form>

                        <!-- Register Form -->
                        <form method="POST" action="<?= $_SERVER['PHP_SELF'] ?>" class="needs-validation d-none" novalidate id="registerForm">
                            <input type="hidden" name="action" value="register">
                            
                            <div class="mb-3">
                                <label class="form-label" for="reg_nama_lengkap">Nama Lengkap</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input class="form-control" id="reg_nama_lengkap" name="nama_lengkap" type="text" 
                                           placeholder="Masukkan nama lengkap" required />
                                    <div class="invalid-feedback">Nama lengkap harus diisi</div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="reg_username">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-at"></i></span>
                                    <input class="form-control" id="reg_username" name="username" type="text" 
                                           placeholder="Masukkan username" required />
                                    <div class="invalid-feedback">Username harus diisi</div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label" for="reg_password">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input class="form-control" id="reg_password" name="password" type="password" 
                                           placeholder="Masukkan password" required />
                                    <button class="btn btn-outline-secondary" type="button" id="toggleRegPassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <div class="invalid-feedback">Password harus diisi</div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label" for="reg_confirm_password">Konfirmasi Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input class="form-control" id="reg_confirm_password" name="confirm_password" type="password" 
                                           placeholder="Konfirmasi password" required />
                                    <button class="btn btn-outline-secondary" type="button" id="toggleRegConfirmPassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <div class="invalid-feedback">Konfirmasi password harus diisi</div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                                <button type="button" class="btn btn-link text-decoration-none" onclick="toggleForms()">Sudah punya akun? Login</button>
                                <button class="btn btn-primary" type="submit">Daftar</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center py-3">
                        <div class="small text-muted">
                            &copy; <?= date('Y') ?> <?= APP_NAME ?> v<?= APP_VERSION ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()

        // Toggle between login and register forms
        function toggleForms() {
            const loginForm = document.getElementById('loginForm');
            const registerForm = document.getElementById('registerForm');
            
            if (loginForm.classList.contains('d-none')) {
                loginForm.classList.remove('d-none');
                registerForm.classList.add('d-none');
            } else {
                loginForm.classList.add('d-none');
                registerForm.classList.remove('d-none');
            }
        }

        // Toggle password visibility for login
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const icon = this.querySelector('i');
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Toggle password visibility for register
        document.getElementById('toggleRegPassword').addEventListener('click', function() {
            const password = document.getElementById('reg_password');
            const icon = this.querySelector('i');
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        document.getElementById('toggleRegConfirmPassword').addEventListener('click', function() {
            const password = document.getElementById('reg_confirm_password');
            const icon = this.querySelector('i');
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>
</body>
</html>