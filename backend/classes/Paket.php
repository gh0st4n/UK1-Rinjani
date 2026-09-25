<?php
class Paket
{
    private $conn;
    private $table = "paket";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAll()
    {
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->table . " ORDER BY id DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->table . " WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function countByJenis($jenis)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM " . $this->table . " WHERE jenis = :jenis");
        $stmt->bindParam(':jenis', $jenis);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] ?? 0;
    }

    // DIPERBARUI: Menambahkan $durasi ke parameter, query, dan execution array
    public function create($nama_paket, $jenis, $harga, $durasi, $kuota, $deskripsi)
    {
        $query = "INSERT INTO " . $this->table . " (nama_paket, jenis, harga, durasi, kuota, deskripsi) VALUES (:nama_paket, :jenis, :harga, :durasi, :kuota, :deskripsi)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':nama_paket' => $nama_paket,
            ':jenis'      => $jenis,
            ':harga'      => $harga,
            ':durasi'     => $durasi,
            ':kuota'      => $kuota,
            ':deskripsi'  => $deskripsi
        ]);
    }

    // DIPERBARUI: Menambahkan $durasi agar fitur edit/update paket juga tersimpan
    public function update($id, $nama_paket, $jenis, $harga, $durasi, $kuota, $deskripsi)
    {
        $query = "UPDATE " . $this->table . " SET nama_paket = :nama_paket, jenis = :jenis, harga = :harga, durasi = :durasi, kuota = :kuota, deskripsi = :deskripsi WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':id'         => $id,
            ':nama_paket' => $nama_paket,
            ':jenis'      => $jenis,
            ':harga'      => $harga,
            ':durasi'     => $durasi,
            ':kuota'      => $kuota,
            ':deskripsi'  => $deskripsi
        ]);
    }

    public function delete($id)
    {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
