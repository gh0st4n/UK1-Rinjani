<?php
class User
{
    private $conn;
    private $table_name = "user";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Method untuk menghapus data user berdasarkan ID
    public function delete($id)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }
}
