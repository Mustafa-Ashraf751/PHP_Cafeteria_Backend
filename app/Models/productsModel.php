<?php
require_once __DIR__ . '/DataBase.php';

class Product
{
    private $db;
    private $table = 'Product';

    
    public function __construct()
    {
        $this->db = DataBase::getDBConnection();
    }

    public function getAll()
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table}");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data) {}

    public function update($id) {}

    public function delete($id) {}
}
