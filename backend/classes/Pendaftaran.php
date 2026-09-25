<?php
class Pendaftaran
{
    private $conn;
    private $table_name = "pendaftaran";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Method untuk menghapus data pendaftaran berdasarkan ID
    public function delete($id)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
