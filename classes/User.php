<?php
// classes/User.php

class User
{
    private $pdo; // PDO инстанция за връзка с базата данни

    // Конструктор: Приема PDO обект за работа с базата
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Метод за регистрация на нов потребител
    public function register($username, $password, $first_name, $last_name, $email, $role = 'client')
    {
        try {
            // ВНИМАНИЕ: Паролите се записват в чист текст по изрично желание, което не е сигурно за продукция.
            $stmt = $this->pdo->prepare("INSERT INTO users (username, password, first_name, last_name, email, role) VALUES (:username, :password, :first_name, :last_name, :email, :role)");
            $result = $stmt->execute([
                ':username' => $username,
                ':password' => $password,
                ':first_name' => $first_name,
                ':last_name' => $last_name,
                ':email' => $email,
                ':role' => $role
            ]);

            return $result;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Метод за вход в системата (Login)
    public function login($username, $password)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = :username");
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch();

            if ($user && $password === $user['password']) {
                return $user;
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Извличане на данни за потребител по ID
    public function getUserById($id)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT id, username, first_name, last_name, email, role FROM users WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    // Извличане на всички потребители с определена роля
    public function getAllByRole($role)
    {
        $stmt = $this->pdo->prepare("SELECT id, username, first_name, last_name, email, role FROM users WHERE role = :role");
        $stmt->execute([':role' => $role]);
        return $stmt->fetchAll();
    }

    // Изтриване на потребител по ID
    public function delete($id)
    {
        try {
            // Защита: Проверка да не се изтрие администраторския акаунт 'admin'
            $check = $this->pdo->prepare("SELECT username FROM users WHERE id = :id");
            $check->execute([':id' => $id]);
            $user = $check->fetch();

            if ($user && $user['username'] === 'admin') {
                return false;
            }

            // Изтриване на записа от таблицата
            $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Обновяване на данните на потребител
    public function update($id, $first_name, $last_name, $email, $role = null)
    {
        try {
            $sql = "UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email";
            $params = [
                ':first_name' => $first_name,
                ':last_name' => $last_name,
                ':email' => $email
            ];

            // Ако е подадена роля, я добавяме към обновяването
            if ($role) {
                $sql .= ", role = :role";
                $params[':role'] = $role;
            }

            $sql .= " WHERE id = :id";
            $params[':id'] = $id;

            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            return false;
        }
    }
}
