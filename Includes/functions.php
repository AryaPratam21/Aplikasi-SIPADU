<?php
// Prevent multiple inclusion
if (function_exists('isLoggedIn')) {
    return;
}

// Fungsi untuk cek login status
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Fungsi untuk redirect
function redirect($url) {
    $base_url = rtrim(BASE_URL, '/');
    $url = ltrim($url, '/');
    $full_url = $base_url . '/' . $url;
    
    if (!headers_sent()) {
        header("Location: $full_url");
        exit();
    }
    
    echo '<script type="text/javascript">';
    echo "window.location.href='$full_url';";
    echo '</script>';
    echo '<noscript>';
    echo "<meta http-equiv='refresh' content='0;url=$full_url'>";
    echo '</noscript>';
    exit();
}

// Fungsi untuk membersihkan input
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Fungsi untuk verifikasi login
function verifyLogin($username, $password, $db) {
    try {
        // Debug input
        error_log("Debug - Login attempt with username: " . $username);
        
        // Cek user di database
        $query = "SELECT * FROM users WHERE username = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Debug user data
        if ($user) {
            error_log("Debug - User found: " . print_r($user, true));
            
            if (password_verify($password, $user['password'])) {
                error_log("Debug - Password verified successfully");
                
                // Set session
                $_SESSION['user_id'] = $user['id_user'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['role'] = $user['role'];
                
                // Log aktivitas login
                logAktivitas($db, $user['id_user'], 'TAMBAH', 'Login ke sistem');
                
                return true;
            }
        }
        
        return false;
    } catch(PDOException $e) {
        error_log("Login Error: " . $e->getMessage());
        return false;
    }
}

// Fungsi untuk logout
function logout() {
    if (isset($_SESSION['user_id'])) {
        try {
            $db = (new Database())->getConnection();
            logAktivitas($db, $_SESSION['user_id'], 'HAPUS', 'Logout dari sistem');
        } catch(Exception $e) {
            error_log("Error logging logout: " . $e->getMessage());
        }
    }
    
    // Destroy session
    session_destroy();
    session_unset();
    
    // Redirect ke login
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit();
}

// Fungsi untuk generate password hash
function generatePasswordHash($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Fungsi untuk memverifikasi password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Fungsi untuk format tanggal Indonesia
function formatTanggal($date) {
    $bulan = array (
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    $split = explode('-', $date);
    return $split[2] . ' ' . $bulan[ (int)$split[1] ] . ' ' . $split[0];
}

// Fungsi untuk cek login status
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Fungsi untuk log aktivitas
function logAktivitas($db, $userId, $aktivitas, $keterangan) {
    try {
        $query = "INSERT INTO log_aktivitas (user_id, aktivitas, keterangan) 
                 VALUES (?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([$userId, $aktivitas, $keterangan]);
        
        return true;
    } catch(PDOException $e) {
        error_log("Error logging activity: " . $e->getMessage());
        return false;
    }
}

// Fungsi untuk format angka ke rupiah
function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Fungsi untuk hitung umur
function hitungUmur($tanggal_lahir) {
    $birthDate = new DateTime($tanggal_lahir);
    $today = new DateTime('today');
    $umur = $today->diff($birthDate);
    return $umur->y;
}

// Fungsi untuk validasi NIK
function validasiNIK($nik) {
    // NIK harus 16 digit
    if (!preg_match("/^[0-9]{16}$/", $nik)) {
        return false;
    }
    return true;
}

// Fungsi untuk generate nomor KK
function generateNoKK($db) {
    try {
        $query = "SELECT MAX(CAST(no_kk AS UNSIGNED)) as max_kk FROM keluarga";
        $stmt = $db->query($query);
        $result = $stmt->fetch();
        
        if ($result['max_kk']) {
            return str_pad($result['max_kk'] + 1, 16, '0', STR_PAD_LEFT);
        }
        return str_pad('1', 16, '0', STR_PAD_LEFT);
    } catch (PDOException $e) {
        error_log("Error generating KK number: " . $e->getMessage());
        return false;
    }
}

// Fungsi untuk generate nomor akta
function generateNoAkta($db, $jenis) {
    try {
        $tahun = date('Y');
        $query = "SELECT MAX(CAST(SUBSTRING_INDEX(no_akta, '/', 1) AS UNSIGNED)) as max_num 
                  FROM $jenis WHERE YEAR(created_at) = :tahun";
        $stmt = $db->prepare($query);
        $stmt->execute([':tahun' => $tahun]);
        $result = $stmt->fetch();
        
        $nomor = ($result['max_num'] ?? 0) + 1;
        return sprintf("%04d/DN/%d", $nomor, $tahun);
    } catch (PDOException $e) {
        error_log("Error generating akta number: " . $e->getMessage());
        return false;
    }
}

// Fungsi untuk format jenis kelamin
function formatJenisKelamin($jk) {
    return $jk === 'L' ? 'Laki-laki' : 'Perempuan';
}

// Fungsi untuk validasi tanggal
function validasiTanggal($tanggal) {
    if (empty($tanggal)) return false;
    
    $d = DateTime::createFromFormat('Y-m-d', $tanggal);
    return $d && $d->format('Y-m-d') === $tanggal;
}

// Fungsi untuk get status aktif
function getStatusAktif($status) {
    $statusList = [
        'AKTIF' => '<span class="badge bg-success">Aktif</span>',
        'MENINGGAL' => '<span class="badge bg-danger">Meninggal</span>',
        'PINDAH' => '<span class="badge bg-warning">Pindah</span>'
    ];
    return $statusList[$status] ?? '<span class="badge bg-secondary">Tidak Diketahui</span>';
}

// Fungsi untuk format ukuran file
function formatUkuranFile($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        return $bytes . ' bytes';
    } elseif ($bytes == 1) {
        return $bytes . ' byte';
    } else {
        return '0 bytes';
    }
}

// Fungsi untuk get extension file
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

// Fungsi untuk validasi file upload
function validasiFileUpload($file, $allowed_types, $max_size) {
    $errors = [];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Error dalam upload file";
    }
    
    if (!in_array($file['type'], $allowed_types)) {
        $errors[] = "Tipe file tidak diizinkan";
    }
    
    if ($file['size'] > $max_size) {
        $errors[] = "Ukuran file terlalu besar (max: " . formatUkuranFile($max_size) . ")";
    }
    
    return $errors;
}

// Tambahkan fungsi untuk debugging
function debug($var, $die = false) {
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
    if ($die) die();
}

// Fungsi untuk mengecek dan membuat direktori
function createDirectory($path) {
    if (!file_exists($path)) {
        return mkdir($path, 0777, true);
    }
    return true;
}

// Fungsi untuk generate random string
function generateRandomString($length = 10) {
    return bin2hex(random_bytes($length));
}

// Fungsi untuk validasi session
function validateSession() {
    if (!isset($_SESSION['last_activity'])) {
        return false;
    }
    
    if (time() - $_SESSION['last_activity'] > 1800) { // 30 menit
        session_unset();
        session_destroy();
        return false;
    }
    
    $_SESSION['last_activity'] = time();
    return true;
}
?>
