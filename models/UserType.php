<?php
namespace app\models;

use PDO;

class UserType {
    public static function syncTable() {
        $db = self::getDb();
        $sql = "CREATE TABLE IF NOT EXISTS user_types (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_type VARCHAR(100) NOT NULL,
            status ENUM('active','inactive') NOT NULL DEFAULT 'active',
            description TEXT DEFAULT NULL,
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
        $stmt = $db->prepare("INSERT INTO user_types (user_type, status, description) VALUES (?, ?, ?)");
        $stmt->execute([
            $data['user_type'],
            $data['status'] ?? 'active',
            $data['description'] ?? null
        ]);
        return $db->lastInsertId();
    }

    public static function find($id) {
        $db = self::getDb();
        $stmt = $db->prepare("SELECT * FROM user_types WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function all() {
        $db = self::getDb();
        return $db->query("SELECT * FROM user_types ORDER BY user_type")->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findByType($userType) {
        $db = self::getDb();
        $stmt = $db->prepare("SELECT * FROM user_types WHERE user_type = ?");
        $stmt->execute([$userType]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function update($id, $data) {
        $db = self::getDb();
        $fields = [];
        $params = [];
        
        foreach (["user_type", "status", "description"] as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $params[] = $id;
        $sql = "UPDATE user_types SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    }

    public static function delete($id) {
        $db = self::getDb();
        $stmt = $db->prepare("DELETE FROM user_types WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function getActiveTypes() {
        $db = self::getDb();
        $stmt = $db->prepare("SELECT * FROM user_types WHERE status = 'active' ORDER BY user_type");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
