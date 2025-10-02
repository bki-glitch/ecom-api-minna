<?php
namespace app\models;

use PDO;

class Service {
    public static function syncTable() {
        $db = self::getDb();
        $sql = "CREATE TABLE IF NOT EXISTS services (
            id INT AUTO_INCREMENT PRIMARY KEY,
            service_name VARCHAR(100) NOT NULL,
            description TEXT DEFAULT NULL,
            image VARCHAR(255) DEFAULT NULL,
            status ENUM('active','inactive') NOT NULL DEFAULT 'active',
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
        $stmt = $db->prepare("INSERT INTO services (service_name, description, image, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $data['service_name'],
            $data['description'] ?? null,
            $data['image'] ?? null,
            $data['status'] ?? 'active'
        ]);
        return $db->lastInsertId();
    }
    public static function find($id) {
        $db = self::getDb();
        $stmt = $db->prepare("SELECT * FROM services WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public static function all() {
        $db = self::getDb();
        return $db->query("SELECT * FROM services ORDER BY service_name")->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function update($id, $data) {
        $db = self::getDb();
        $fields = [];
        $params = [];
        foreach (["service_name", "description", "image", "status"] as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        if (!$fields) return false;
        $params[] = $id;
        $sql = "UPDATE services SET ".implode(", ", $fields).", updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    }
    public static function delete($id) {
        $db = self::getDb();
        $stmt = $db->prepare("DELETE FROM services WHERE id = ?");
        return $stmt->execute([$id]);
    }
    public static function count() {
        $db = self::getDb();
        return (int)$db->query("SELECT COUNT(*) FROM services")->fetchColumn();
    }
}
