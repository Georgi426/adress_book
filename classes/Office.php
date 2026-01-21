<?php
// classes/Office.php

class Office
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAll()
    {
        $stmt = $this->pdo->query("SELECT * FROM offices");
        return $stmt->fetchAll();
    }

    public function add($location_name, $address, $phone)
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO offices (location_name, address, phone) VALUES (?, ?, ?)");
            return $stmt->execute([$location_name, $address, $phone]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function update($id, $location_name, $address, $phone)
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE offices SET location_name = ?, address = ?, phone = ? WHERE id = ?");
            return $stmt->execute([$location_name, $address, $phone, $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM offices WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM offices WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
