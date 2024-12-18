<?php
// Load constants if not already loaded
if (!defined('DB_HOST')) {
    require_once __DIR__ . '/constants.php';
}

class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            // Buat database jika belum ada
            $temp = new PDO("mysql:host=" . $this->host, $this->username, $this->password);
            $temp->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $temp->exec("CREATE DATABASE IF NOT EXISTS " . $this->db_name);
            
            // Koneksi ke database yang sudah dibuat
            $dsn = "mysql:host=" . $this->host . 
                   ";dbname=" . $this->db_name . 
                   ";charset=utf8mb4";
                
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];
                
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
            return $this->conn;
        } catch(PDOException $e) {
            error_log("Connection Error: " . $e->getMessage());
            throw new Exception("Koneksi database gagal");
        }
    }

    public function fetchOne($query, $params = []) {
        try {
            if (!$this->conn) {
                $this->getConnection();
            }
            
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt->execute($params)) {
                throw new Exception("Query execution failed");
            }
            
            $result = $stmt->fetch();
            $stmt = null;
            
            return $result;
            
        } catch (PDOException $e) {
            error_log("Query Error: " . $e->getMessage());
            throw new Exception("Gagal mengambil data");
        }
    }

    public function fetchAll($query, $params = []) {
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Query Error: " . $e->getMessage());
            throw new Exception("Gagal mengambil data");
        }
    }

    public function beginTransaction() {
        return $this->conn->beginTransaction();
    }

    public function commit() {
        return $this->conn->commit();
    }

    public function rollback() {
        return $this->conn->rollBack();
    }
}
?>
