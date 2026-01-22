<?php
// classes/User.php

class User
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function register($username, $password, $first_name, $last_name, $email, $role = 'client')
    {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("INSERT INTO users (username, password, first_name, last_name, email, role) VALUES (?, ?, ?, ?, ?, ?)");
            $result = $stmt->execute([$username, $hashed_password, $first_name, $last_name, $email, $role]);

            if ($result) {
                // Registration successful
            }

            return $result;
        } catch (PDOException $e) {
            // Handle duplicate entry or other errors
            return false;
        }
    }

    public function login($username, $password)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                return $user;
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getUserById($id)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT id, username, first_name, last_name, email, role FROM users WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getAllByRole($role)
    {
        $stmt = $this->pdo->prepare("SELECT id, username, first_name, last_name, email, role FROM users WHERE role = ?");
        $stmt->execute([$role]);
        return $stmt->fetchAll();
    }

    public function delete($id)
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function update($id, $first_name, $last_name, $email, $role = null)
    {
        try {
            $sql = "UPDATE users SET first_name = ?, last_name = ?, email = ?";
            $params = [$first_name, $last_name, $email];

            if ($role) {
                $sql .= ", role = ?";
                $params[] = $role;
            }

            $sql .= " WHERE id = ?";
            $params[] = $id;

            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            return false;
        }
    }
}
