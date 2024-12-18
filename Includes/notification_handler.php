<?php
// Prevent direct access
if (!defined('BASE_PATH')) {
    die('Direct access not permitted');
}

// Definisikan konstanta email jika belum ada
if (!defined('ADMIN_EMAIL')) {
    define('ADMIN_EMAIL', 'admin@example.com'); // Email default
}

if (!defined('SMTP_FROM')) {
    define('SMTP_FROM', 'noreply@example.com'); // Email pengirim default
}

class NotificationHandler {
    private $db;
    private $admin_email;
    private $smtp_from;
    
    public function __construct($db) {
        $this->db = $db;
        $this->admin_email = defined('ADMIN_EMAIL') ? ADMIN_EMAIL : 'admin@example.com';
        $this->smtp_from = defined('SMTP_FROM') ? SMTP_FROM : 'noreply@example.com';
    }
    
    // Fungsi untuk menambah notifikasi
    public function addNotification($type, $message, $level = 'info') {
        try {
            $query = "INSERT INTO notifications (type, message, level, created_at) 
                     VALUES (:type, :message, :level, NOW())";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':type' => $type,
                ':message' => $message,
                ':level' => $level
            ]);
            
            // Jika error kritis, kirim email ke admin
            if ($level === 'critical') {
                $this->notifyAdmin($type, $message);
            }
            
            return true;
        } catch (PDOException $e) {
            error_log("Error adding notification: " . $e->getMessage());
            return false;
        }
    }
    
    // Fungsi untuk mengirim email ke admin
    private function notifyAdmin($type, $message) {
        try {
            // Gunakan property class untuk email
            $to = $this->admin_email;
            $subject = "[" . APP_NAME . "] Error Kritis: " . $type;
            
            $body = "Error kritis terdeteksi di " . APP_NAME . "\n\n";
            $body .= "Tipe: " . $type . "\n";
            $body .= "Pesan: " . $message . "\n";
            $body .= "Waktu: " . date('Y-m-d H:i:s') . "\n";
            $body .= "Server: " . $_SERVER['SERVER_NAME'] . "\n";
            
            $headers = "From: " . APP_NAME . " <" . $this->smtp_from . ">\r\n";
            $headers .= "Reply-To: " . $this->admin_email . "\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
            
            // Tambahkan log sebelum mengirim email
            error_log("Sending notification email to: " . $to);
            
            if (!mail($to, $subject, $body, $headers)) {
                throw new Exception("Failed to send email notification");
            }
            
            // Log sukses
            error_log("Notification email sent successfully");
            
        } catch (Exception $e) {
            error_log("Error sending admin notification: " . $e->getMessage());
            // Tidak throw exception agar tidak menghentikan proses
        }
    }
    
    // Fungsi untuk mengambil notifikasi yang belum dibaca
    public function getUnreadNotifications() {
        try {
            $query = "SELECT * FROM notifications 
                     WHERE is_read = 0 
                     ORDER BY created_at DESC 
                     LIMIT 10";
            
            $stmt = $this->db->query($query);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting notifications: " . $e->getMessage());
            return [];
        }
    }
    
    // Fungsi untuk menandai notifikasi sudah dibaca
    public function markAsRead($id) {
        try {
            $query = "UPDATE notifications 
                     SET is_read = 1, 
                         read_at = NOW() 
                     WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Error marking notification as read: " . $e->getMessage());
            return false;
        }
    }
    
    // Fungsi untuk membersihkan notifikasi lama
    public function cleanOldNotifications($days = 30) {
        try {
            $query = "DELETE FROM notifications 
                     WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY) 
                     AND is_read = 1";
            
            $stmt = $this->db->prepare($query);
            return $stmt->execute([':days' => $days]);
        } catch (PDOException $e) {
            error_log("Error cleaning old notifications: " . $e->getMessage());
            return false;
        }
    }
}

// Struktur tabel notifications
/*
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    level ENUM('info', 'warning', 'critical') DEFAULT 'info',
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME NOT NULL,
    read_at DATETIME NULL
);
*/

// Contoh penggunaan:
/*
$notification = new NotificationHandler($db);

// Menambah notifikasi error kritis
$notification->addNotification(
    'Database Error',
    'Koneksi database gagal pada modul penduduk',
    'critical'
);

// Mengambil notifikasi belum dibaca
$unread = $notification->getUnreadNotifications();

// Menandai notifikasi sudah dibaca
$notification->markAsRead(1);

// Membersihkan notifikasi lama
$notification->cleanOldNotifications();
*/