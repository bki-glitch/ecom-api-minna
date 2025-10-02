<?php
namespace app\controllers;

use app\models\User;

class UserController {
    public static function register($req, $res) {
        $data = $req->body;
        if (!isset($data['first_name'], $data['last_name'], $data['email'], $data['password'])) {
            return $res->json(['error' => 'Missing required fields'], 400);
        }
        
        // Validate user_type_id if provided
        if (isset($data['user_type_id']) && !User::validateUserType($data['user_type_id'])) {
            return $res->json(['error' => 'Invalid user type ID or user type is inactive'], 400);
        }
        
        // Validate contact number format if provided
        if (isset($data['contact_number']) && !empty($data['contact_number'])) {
            if (!preg_match('/^[\+]?[0-9\-\(\)\s]{7,20}$/', $data['contact_number'])) {
                return $res->json(['error' => 'Invalid contact number format'], 400);
            }
        }
        
        if (User::findByEmail($data['email'])) {
            return $res->json(['error' => 'Email already exists'], 409);
        }
        
        // Hash password before saving
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $id = User::create($data);
        $user = User::findWithUserType($id);
        if ($user) unset($user['password']);
        $res->json($user, 201);
    }
    public static function login($req, $res) {
        $data = $req->body;
        $user = User::findByEmail($data['email'] ?? '');
        if (!$user || !password_verify($data['password'] ?? '', $user['password'])) {
            return $res->json(['error' => 'Invalid credentials']);
        }

        $jwt = self::generateJWT($user['id'], $user['email']);
        unset($user['password']);
        $res->json(['user' => $user, 'token' => $jwt]);
    }
    public static function index($req, $res) {
        $users = User::getAllWithUserType();
        foreach ($users as &$u) unset($u['password']);
        $res->json($users);
    }
    public static function show($req, $res) {
        $user = User::findWithUserType($req->params[0]);
        if ($user) unset($user['password']);
        $res->json($user ?: ['error' => 'User not found']);
    }
    public static function update($req, $res) {
        $id = $req->params[0];
        $data = $req->body;
        
        // Validate user_type_id if provided
        if (isset($data['user_type_id']) && !User::validateUserType($data['user_type_id'])) {
            return $res->json(['error' => 'Invalid user type ID or user type is inactive']);
        }
        
        // Validate contact number format if provided
        if (isset($data['contact_number']) && !empty($data['contact_number'])) {
            if (!preg_match('/^[\+]?[0-9\-\(\)\s]{7,20}$/', $data['contact_number'])) {
                return $res->json(['error' => 'Invalid contact number format']);
            }
        }
        
        $ok = User::update($id, $data);
        $user = User::findWithUserType($id);
        if ($user) unset($user['password']);
        $res->json($user ?: ['error' => 'User not found']);
    }
    public static function delete($req, $res) {
        $ok = User::delete($req->params[0]);
        $res->json(['success' => $ok]);
    }
    public static function verifyOtp($req, $res) {
        $data = $req->body;
        if (!isset($data['email'], $data['otp'])) {
            return $res->json(['error' => 'Email and OTP required'], 400);
        }
        $user = \app\models\User::findByEmail($data['email']);
        if (!$user || !$user['otp'] || $user['otp'] !== $data['otp']) {
            return $res->json(['error' => 'Invalid OTP'], 400);
        }
        // Clear OTP after successful verification
        \app\models\User::update($user['id'], ['otp' => null]);
        $res->json(['success' => true, 'message' => 'OTP verified and cleared']);
    }
    public static function resendOtp($req, $res) {
        $data = $req->body;
        if (!isset($data['email'])) {
            return $res->json(['error' => 'Email required']);
        }
        $user = \app\models\User::findByEmail($data['email']);
        if (!$user) {
            return $res->json(['error' => 'User not found']);
        }
        $otp = rand(100000, 999999);
        \app\models\User::update($user['id'], ['otp' => $otp]);
        // Here you would send the OTP via email/SMS in a real app
        $res->json(['success' => true, 'otp' => $otp, 'message' => 'OTP resent']);
    }
    public static function resetPassword($req, $res) {
        $data = $req->body;
        if (!isset($data['email'], $data['otp'], $data['new_password'])) {
            return $res->json(['error' => 'Email, OTP, and new password required'], 400);
        }
        $user = \app\models\User::findByEmail($data['email']);
        if (!$user || !$user['otp'] || $user['otp'] !== $data['otp']) {
            return $res->json(['error' => 'Invalid OTP'], 400);
        }
        \app\models\User::update($user['id'], [
            'password' => password_hash($data['new_password'], PASSWORD_DEFAULT),
            'otp' => null,
            'is_password_reset' => 1
        ]);
        $res->json(['success' => true, 'message' => 'Password reset successful']);
    }
    private static function generateJWT($userId, $email) {
        $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode([
            'sub' => $userId,
            'email' => $email,
            'iat' => time(),
            'exp' => time() + 3600
        ]));
        $secret = 'your_jwt_secret';
        $signature = base64_encode(hash_hmac('sha256', "$header.$payload", $secret, true));
        return "$header.$payload.$signature";
    }
}
