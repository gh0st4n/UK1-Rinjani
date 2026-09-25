<?php
// Mitigasi Clickjacking
if (!headers_sent()) {
    header('X-Frame-Options: DENY');
    header("Content-Security-Policy: frame-ancestors 'none';");
}

class Database 
{
    private string $host = "localhost";
    private string $dbName = "travel_haji_umroh";
    private string $username = "root";
    private string $password = "";
    private ?PDO $conn = null;

    public function getConnection(): ?PDO 
    {
        $this->conn = null;

        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbName};charset=utf8mb4";
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            // Hindari mengekspos credential saat error di lingkungan produksi
            die("Koneksi Database Error: " . $e->getMessage());
        }

        return $this->conn;
    }
}