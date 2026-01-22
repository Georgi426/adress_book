<?php
class Contact
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Create a new contact
    public function create($user_id, $data)
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO contacts 
                (user_id, first_name, last_name, company_name, address, phone_landline, phone_mobile, email, fax, note) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $result = $stmt->execute([
                $user_id,
                $data['first_name'],
                $data['last_name'],
                $data['company_name'] ?? null,
                $data['address'] ?? null,
                $data['phone_landline'] ?? null,
                $data['phone_mobile'] ?? null,
                $data['email'] ?? null,
                $data['fax'] ?? null,
                $data['note'] ?? null
            ]);

            if ($result) {
                return $this->pdo->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Update existing contact
    public function update($id, $user_id, $data)
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE contacts SET
                first_name = ?, last_name = ?, company_name = ?, address = ?, 
                phone_landline = ?, phone_mobile = ?, email = ?, fax = ?, note = ?
                WHERE id = ? AND user_id = ?
            ");
            return $stmt->execute([
                $data['first_name'],
                $data['last_name'],
                $data['company_name'] ?? null,
                $data['address'] ?? null,
                $data['phone_landline'] ?? null,
                $data['phone_mobile'] ?? null,
                $data['email'] ?? null,
                $data['fax'] ?? null,
                $data['note'] ?? null,
                $id,
                $user_id
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Delete contact
    public function delete($id, $user_id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM contacts WHERE id = ? AND user_id = ?");
        return $stmt->execute([$id, $user_id]);
    }

    // Get single contact by ID
    public function getById($id, $user_id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM contacts WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get all contacts for user
    public function getAll($user_id, $search = null)
    {
        $sql = "SELECT * FROM contacts WHERE user_id = ?";
        $params = [$user_id];

        if ($search) {
            $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR company_name LIKE ?)";
            $term = "%$search%";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $sql .= " ORDER BY first_name ASC, last_name ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- Tags ---

    public function attachTag($contact_id, $tag_id)
    {
        $stmt = $this->pdo->prepare("INSERT IGNORE INTO contact_tags (contact_id, tag_id) VALUES (?, ?)");
        return $stmt->execute([$contact_id, $tag_id]);
    }

    public function detachTag($contact_id, $tag_id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM contact_tags WHERE contact_id = ? AND tag_id = ?");
        return $stmt->execute([$contact_id, $tag_id]);
    }

    public function getTags($contact_id)
    {
        $stmt = $this->pdo->prepare("
            SELECT t.* FROM tags t
            JOIN contact_tags ct ON t.id = ct.tag_id
            WHERE ct.contact_id = ?
        ");
        $stmt->execute([$contact_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- Custom Fields ---

    public function getCustomFields($contact_id)
    {
        $stmt = $this->pdo->prepare("
            SELECT cf.id as def_id, cf.field_name, cf.field_type, cv.field_value as value, cv.id as value_id
            FROM custom_field_definitions cf
            LEFT JOIN custom_field_values cv ON cf.id = cv.field_def_id AND cv.contact_id = ?
            WHERE cf.user_id = (SELECT user_id FROM contacts WHERE id = ?)
        ");
        $stmt->execute([$contact_id, $contact_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveCustomField($contact_id, $def_id, $value)
    {
        // Check if value exists
        $stmt = $this->pdo->prepare("SELECT id FROM custom_field_values WHERE contact_id = ? AND field_def_id = ?");
        $stmt->execute([$contact_id, $def_id]);
        $existing = $stmt->fetch();

        if ($existing) {
            $update = $this->pdo->prepare("UPDATE custom_field_values SET field_value = ? WHERE id = ?");
            return $update->execute([$value, $existing['id']]);
        } else {
            $insert = $this->pdo->prepare("INSERT INTO custom_field_values (contact_id, field_def_id, field_value) VALUES (?, ?, ?)");
            return $insert->execute([$contact_id, $def_id, $value]);
        }
    }
}
