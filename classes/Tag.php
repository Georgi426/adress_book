<?php
class Tag
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Създаване на нов етикет с име и цвят
    public function create($user_id, $name, $color)
    {
        $stmt = $this->pdo->prepare("INSERT INTO tags (user_id, name, color) VALUES (:user_id, :name, :color)");
        return $stmt->execute([':user_id' => $user_id, ':name' => $name, ':color' => $color]);
    }

    // Обновяване на съществуващ етикет
    public function update($id, $user_id, $name, $color)
    {
        $stmt = $this->pdo->prepare("UPDATE tags SET name = :name, color = :color WHERE id = :id AND user_id = :user_id");
        return $stmt->execute([':name' => $name, ':color' => $color, ':id' => $id, ':user_id' => $user_id]);
    }

    // Изтриване на етикет
    public function delete($id, $user_id)
    {
        // При изтриване на етикета, връзките в contact_tags ще се изтрият автоматично (ако има ON DELETE CASCADE в базата)
        // или ще останат сираци, но тук просто трием дефиницията.
        $stmt = $this->pdo->prepare("DELETE FROM tags WHERE id = :id AND user_id = :user_id");
        return $stmt->execute([':id' => $id, ':user_id' => $user_id]);
    }

    // Вземане на всички етикети на потребителя
    public function getAll($user_id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM tags WHERE user_id = :user_id ORDER BY name ASC");
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Вземане на един етикет по ID
    public function getById($id, $user_id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM tags WHERE id = :id AND user_id = :user_id");
        $stmt->execute([':id' => $id, ':user_id' => $user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
