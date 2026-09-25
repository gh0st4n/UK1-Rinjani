<?php
// Mitigasi Clickjacking
header('X-Frame-Options: Deny');
header("Content-Security-Policy: frame-ancestors 'none';");

class Database
{
    private $host = "localhost";
    private $db_name = "travel_haji_umroh";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection()
    {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $exception) {
            die("Koneksi Database Error: " . $exception->getMessage());
        }
        return $this->conn;
    }
}
