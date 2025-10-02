<?php
namespace app\models;

use PDO;

class User {
    public static function syncTable() {
        $db = self::getDb();
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            user_type_id INT DEFAULT NULL,
            contact_number VARCHAR(20) DEFAULT NULL,
            address TEXT DEFAULT NULL,
            status ENUM('active','inactive') NOT NULL DEFAULT 'active',
            otp VARCHAR(10) DEFAULT NULL,
            is_password_reset BOOLEAN DEFAULT 0,
            remarks TEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_type_id) REFERENCES user_types(id) ON DELETE SET NULL
        )";
        $db->exec($sql);
    }
    private static function getDb() {
        $config = require __DIR__ . '/../config/database.php';
        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8";
        return new PDO($dsn, $config['user'], $config['pass']);
    }
    // CRUD methods for new fields
    public static function create($data) {
        $db = self::getDb();
        $stmt = $db->prepare("INSERT INTO users (first_name, last_name, email, password, user_type_id, contact_number, address, status, otp, is_password_reset, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['user_type_id'] ?? null,
            $data['contact_number'] ?? null,
            $data['address'] ?? null,
            $data['status'] ?? 'active',
            $data['otp'] ?? null,
            $data['is_password_reset'] ?? 0,
            $data['remarks'] ?? null
        ]);
        return $db->lastInsertId();
    }
    public static function findByEmail($email) {
        $db = self::getDb();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public static function find($id) {
        $db = self::getDb();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public static function all() {
        $db = self::getDb();
        return $db->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function update($id, $data) {
        $db = self::getDb();
        $fields = [];
        $params = [];
        foreach (["first_name","last_name","email","user_type_id","contact_number","address","status","otp","is_password_reset","remarks"] as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        if (isset($data['password'])) {
            $fields[] = "password = ?";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        if (empty($fields)) {
            // Nothing to update
            return false;
        }
        $params[] = $id;
        $sql = "UPDATE users SET ".implode(",", $fields)." WHERE id = ?";
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    }
    public static function delete($id) {
        $db = self::getDb();
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public static function getAllWithUserType() {
        $db = self::getDb();
        $sql = "SELECT u.*, ut.user_type, ut.description as user_type_description 
                FROM users u 
                LEFT JOIN user_types ut ON u.user_type_id = ut.id 
                ORDER BY u.created_at DESC";
        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public static function findWithUserType($id) {
        $db = self::getDb();
        $stmt = $db->prepare("SELECT u.*, ut.user_type, ut.description as user_type_description 
                             FROM users u 
                             LEFT JOIN user_types ut ON u.user_type_id = ut.id 
                             WHERE u.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public static function validateUserType($userTypeId) {
        if ($userTypeId === null) return true; // Allow null values
        
        $db = self::getDb();
        $stmt = $db->prepare("SELECT id FROM user_types WHERE id = ? AND status = 'active'");
        $stmt->execute([$userTypeId]);
        return $stmt->fetch() !== false;
    }
}
