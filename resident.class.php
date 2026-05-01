<?php
require_once 'connect.php';

class Resident {
    private $link;
    private $table = "residents";

    public function __construct() {
        global $link;
        $this->link = $link;
    }

    // Add new resident
    public function add($data) {
        $sql = "INSERT INTO " . $this->table . " 
                (full_name, dob, nic, address, phone, email, occupation, gender) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->link->prepare($sql);
        $stmt->bind_param(
            "ssssssss",
            $data['full_name'],
            $data['dob'],
            $data['nic'],
            $data['address'],
            $data['phone'],
            $data['email'],
            $data['occupation'],
            $data['gender']
        );
        
        if ($stmt->execute()) {
            return ['success' => true, 'id' => $this->link->insert_id];
        } else {
            return ['success' => false, 'error' => $stmt->error];
        }
    }

    // Update resident
    public function update($id, $data) {
        $sql = "UPDATE " . $this->table . " SET 
                full_name = ?, dob = ?, nic = ?, address = ?, 
                phone = ?, email = ?, occupation = ?, gender = ? 
                WHERE id = ?";
        
        $stmt = $this->link->prepare($sql);
        $stmt->bind_param(
            "ssssssssi",
            $data['full_name'],
            $data['dob'],
            $data['nic'],
            $data['address'],
            $data['phone'],
            $data['email'],
            $data['occupation'],
            $data['gender'],
            $id
        );
        
        return $stmt->execute();
    }

    // Delete resident
    public function delete($id) {
        $stmt = $this->link->prepare("DELETE FROM " . $this->table . " WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // Get single resident by ID
    public function getById($id) {
        $stmt = $this->link->prepare("SELECT * FROM " . $this->table . " WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Search residents
    public function search($searchTerm) {
        $searchTerm = "%" . $searchTerm . "%";
        
        $sql = "SELECT * FROM " . $this->table . " WHERE 
                full_name LIKE ? OR 
                nic LIKE ? OR 
                address LIKE ? OR 
                phone LIKE ? OR 
                email LIKE ?
                ORDER BY full_name ASC";
        
        $stmt = $this->link->prepare($sql);
        $stmt->bind_param("sssss", 
            $searchTerm, $searchTerm, $searchTerm, 
            $searchTerm, $searchTerm
        );
        $stmt->execute();
        $result = $stmt->get_result();
        
        $residents = [];
        while ($row = $result->fetch_assoc()) {
            $residents[] = $row;
        }
        
        return $residents;
    }

    // Get all residents
    public function getAll() {
        $result = $this->link->query("SELECT * FROM " . $this->table . " ORDER BY full_name ASC");
        $residents = [];
        while ($row = $result->fetch_assoc()) {
            $residents[] = $row;
        }
        return $residents;
    }

    // Check if NIC exists (for validation)
    public function nicExists($nic, $excludeId = null) {
        $sql = "SELECT id FROM " . $this->table . " WHERE nic = ?";
        if ($excludeId) {
            $sql .= " AND id != ?";
            $stmt = $this->link->prepare($sql);
            $stmt->bind_param("si", $nic, $excludeId);
        } else {
            $stmt = $this->link->prepare($sql);
            $stmt->bind_param("s", $nic);
        }
        
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }
}
?>