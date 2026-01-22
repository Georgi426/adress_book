<?php
class Tag
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function create($user_id, $name, $color)
    {
        $stmt = $this->pdo->prepare("INSERT INTO tags (user_id, name, color) VALUES (?, ?, ?)");
        return $stmt->execute([$user_id, $name, $color]);
    }

    public function update($id, $user_id, $name, $color)
    {
        $stmt = $this->pdo->prepare("UPDATE tags SET name = ?, color = ? WHERE id = ? AND user_id = ?");
        return $stmt->execute([$name, $color, $id, $user_id]);
    }

    public function delete($id, $user_id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM tags WHERE id = ? AND user_id = ?");
        return $stmt->execute([$id, $user_id]);
    }

    public function getAll($user_id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM tags WHERE user_id = ? ORDER BY name ASC");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id, $user_id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM tags WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
