<?php
namespace app\models;

use PDO;

class ContactForm {
    public static function syncTable() {
        $db = self::getDb();
        $sql = "CREATE TABLE IF NOT EXISTS contact_forms (
            id INT AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            contact_number VARCHAR(20) DEFAULT NULL,
            email VARCHAR(150) NOT NULL,
            message TEXT NOT NULL,
            status ENUM('unread','read','replied') NOT NULL DEFAULT 'unread',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $db->exec($sql);
    }

    private static function getDb() {
        $config = require __DIR__ . '/../config/database.php';
        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8";
        return new PDO($dsn, $config['user'], $config['pass']);
    }

    public static function create($data) {
        $db = self::getDb();
        $stmt = $db->prepare("INSERT INTO contact_forms (first_name, last_name, contact_number, email, message, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['contact_number'] ?? null,
            $data['email'],
            $data['message'],
            $data['status'] ?? 'unread'
        ]);
        return $db->lastInsertId();
    }

    public static function find($id) {
        $db = self::getDb();
        $stmt = $db->prepare("SELECT * FROM contact_forms WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function all() {
        $db = self::getDb();
        return $db->query("SELECT * FROM contact_forms ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function update($id, $data) {
        $db = self::getDb();
        $fields = [];
        $params = [];
        foreach (["first_name","last_name","contact_number","email","message","status"] as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        if (empty($fields)) return false;
        $params[] = $id;
        $sql = "UPDATE contact_forms SET " . implode(", ", $fields) . ", updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    }

    public static function delete($id) {
        $db = self::getDb();
        $stmt = $db->prepare("DELETE FROM contact_forms WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
