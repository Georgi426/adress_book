<?php
// classes/Shipment.php

class Shipment
{
    private $pdo;

    // Constants for pricing
    const RATE_PER_KG = 5.00; // Base rate per kg
    const OFFICE_FEE = 2.00;
    const ADDRESS_FEE = 10.00;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function calculatePrice($weight, $is_to_address)
    {
        $base = $weight * self::RATE_PER_KG;
        $delivery_fee = $is_to_address ? self::ADDRESS_FEE : self::OFFICE_FEE;
        return $base + $delivery_fee;
    }

    public function register($sender_id, $receiver_phone, $from_office_id, $to_office_id, $to_address, $weight)
    {
        try {
            // Determine if delivery is to address
            $is_to_address = !empty($to_address);
            $price = $this->calculatePrice($weight, $is_to_address);

            // Try to find receiver by phone if exists (optional logic)
            // For now, we just proceed. If we had a requirement to link by phone, we would query Users here.

            $sql = "INSERT INTO shipments (sender_id, receiver_phone, from_office_id, to_office_id, to_address, weight, price, status, date_created) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'registered', NOW())";

            $stmt = $this->pdo->prepare($sql);
            // If to_address is used, to_office_id should be NULL or we treat it as such in DB logic
            $to_office_val = $to_office_id ? $to_office_id : null;
            $to_addr_val = $to_address ? $to_address : null;

            return $stmt->execute([$sender_id, $receiver_phone, $from_office_id, $to_office_val, $to_addr_val, $weight, $price]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getAll()
    {
        // Build query to fetch names if possible
        $sql = "SELECT s.*, 
                       u1.username as sender_name, 
                       o1.location_name as from_office_name,
                       o2.location_name as to_office_name
                FROM shipments s
                LEFT JOIN users u1 ON s.sender_id = u1.id
                LEFT JOIN offices o1 ON s.from_office_id = o1.id
                LEFT JOIN offices o2 ON s.to_office_id = o2.id
                ORDER BY s.date_created DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    public function getBySenderId($sender_id)
    {
        $sql = "SELECT s.*, 
                       o1.location_name as from_office_name,
                       o2.location_name as to_office_name
                FROM shipments s
                LEFT JOIN offices o1 ON s.from_office_id = o1.id
                LEFT JOIN offices o2 ON s.to_office_id = o2.id
                WHERE s.sender_id = ?
                ORDER BY s.date_created DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$sender_id]);
        return $stmt->fetchAll();
    }

    public function updateStatus($id, $status)
    {
        $stmt = $this->pdo->prepare("UPDATE shipments SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
}
