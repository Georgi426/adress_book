<?php
class Contact
{
    private $pdo; // Връзка с базата данни

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Създаване на нов контакт
    public function create($user_id, $data)
    {
        try {
            // Подготвяне на заявка с именувани параметри (по-четимо от ?)
            $stmt = $this->pdo->prepare("
                INSERT INTO contacts 
                (user_id, first_name, last_name, company_name, address, phone_landline, phone_mobile, email, fax, note) 
                VALUES (:user_id, :first_name, :last_name, :company_name, :address, :phone_landline, :phone_mobile, :email, :fax, :note)
            ");
            // Изпълнение на заявката
            $result = $stmt->execute([
                ':user_id' => $user_id,
                ':first_name' => $data['first_name'],
                ':last_name' => $data['last_name'],
                ':company_name' => $data['company_name'] ?? null,
                ':address' => $data['address'] ?? null,
                ':phone_landline' => $data['phone_landline'] ?? null,
                ':phone_mobile' => $data['phone_mobile'] ?? null,
                ':email' => $data['email'] ?? null,
                ':fax' => $data['fax'] ?? null,
                ':note' => $data['note'] ?? null
            ]);

            if ($result) {
                return $this->pdo->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Обновяване на съществуващ контакт
    public function update($id, $user_id, $data)
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE contacts SET
                first_name = :first_name, 
                last_name = :last_name, 
                company_name = :company_name, 
                address = :address, 
                phone_landline = :phone_landline, 
                phone_mobile = :phone_mobile, 
                email = :email, 
                fax = :fax, 
                note = :note
                WHERE id = :id AND user_id = :user_id
            ");
            return $stmt->execute([
                ':first_name' => $data['first_name'],
                ':last_name' => $data['last_name'],
                ':company_name' => $data['company_name'] ?? null,
                ':address' => $data['address'] ?? null,
                ':phone_landline' => $data['phone_landline'] ?? null,
                ':phone_mobile' => $data['phone_mobile'] ?? null,
                ':email' => $data['email'] ?? null,
                ':fax' => $data['fax'] ?? null,
                ':note' => $data['note'] ?? null,
                ':id' => $id,
                ':user_id' => $user_id
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Изтриване на контакт
    public function delete($id, $user_id)
    {
        // Изтрива контакт само ако принадлежи на потребителя
        $stmt = $this->pdo->prepare("DELETE FROM contacts WHERE id = :id AND user_id = :user_id");
        return $stmt->execute([':id' => $id, ':user_id' => $user_id]);
    }

    // Вземане на един контакт по ID
    public function getById($id, $user_id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM contacts WHERE id = :id AND user_id = :user_id");
        $stmt->execute([':id' => $id, ':user_id' => $user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Вземане на всички контакти за потребител (с опция за търсене)
    public function getAll($user_id, $search = null)
    {
        $sql = "SELECT * FROM contacts WHERE user_id = :user_id";
        $params = [':user_id' => $user_id];

        // Ако има дума за търсене, добавяме условие
        if ($search) {
            $sql .= " AND (first_name LIKE :search OR last_name LIKE :search OR email LIKE :search OR company_name LIKE :search)";
            $params[':search'] = "%$search%";
        }

        $sql .= " ORDER BY first_name ASC, last_name ASC"; // Сортиране по име

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- Етикети (Tags) ---

    // Прикачване на етикет към контакт
    public function attachTag($contact_id, $tag_id)
    {
        // INSERT IGNORE предотвратява грешки при дублиране
        $stmt = $this->pdo->prepare("INSERT IGNORE INTO contact_tags (contact_id, tag_id) VALUES (:contact_id, :tag_id)");
        return $stmt->execute([':contact_id' => $contact_id, ':tag_id' => $tag_id]);
    }

    // Премахване на етикет от контакт
    public function detachTag($contact_id, $tag_id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM contact_tags WHERE contact_id = :contact_id AND tag_id = :tag_id");
        return $stmt->execute([':contact_id' => $contact_id, ':tag_id' => $tag_id]);
    }

    // Вземане на всички етикети за даден контакт
    public function getTags($contact_id)
    {
        $stmt = $this->pdo->prepare("
            SELECT t.* FROM tags t
            JOIN contact_tags ct ON t.id = ct.tag_id
            WHERE ct.contact_id = :contact_id
        ");
        $stmt->execute([':contact_id' => $contact_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- Допълнителни полета (Custom Fields) ---

    // Вземане на дефинициите и стойностите на допълнителните полета
    public function getCustomFields($contact_id)
    {
        // LEFT JOIN свързва дефинициите със стойностите за конкретния контакт
        $stmt = $this->pdo->prepare("
            SELECT cf.id as def_id, cf.field_name, cf.field_type, cv.field_value as value, cv.id as value_id
            FROM custom_field_definitions cf
            LEFT JOIN custom_field_values cv ON cf.id = cv.field_def_id AND cv.contact_id = :contact_id_val
            WHERE cf.user_id = (SELECT user_id FROM contacts WHERE id = :contact_id_sub)
        ");
        $stmt->execute([':contact_id_val' => $contact_id, ':contact_id_sub' => $contact_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Запазване или обновяване на стойност за допълнително поле
    public function saveCustomField($contact_id, $def_id, $value)
    {
        // Проверка дали вече има запис за това поле и контакт
        $stmt = $this->pdo->prepare("SELECT id FROM custom_field_values WHERE contact_id = :contact_id AND field_def_id = :def_id");
        $stmt->execute([':contact_id' => $contact_id, ':def_id' => $def_id]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Ако има - обновяваме стойността
            $update = $this->pdo->prepare("UPDATE custom_field_values SET field_value = :value WHERE id = :id");
            return $update->execute([':value' => $value, ':id' => $existing['id']]);
        } else {
            // Ако няма - създаваме нов запис
            $insert = $this->pdo->prepare("INSERT INTO custom_field_values (contact_id, field_def_id, field_value) VALUES (:contact_id, :def_id, :value)");
            return $insert->execute([':contact_id' => $contact_id, ':def_id' => $def_id, ':value' => $value]);
        }
    }
}
