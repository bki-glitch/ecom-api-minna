<?php
namespace app\controllers;

use app\models\UserType;
use \Exception;

class UserTypeController {
    
    public static function index($req, $res) {
        $userTypes = UserType::all();
        $res->json($userTypes);
    }

    public static function show($req, $res) {
        $id = $req->params[0];
        $userType = UserType::find($id);
        
        if (!$userType) {
            return $res->json(['error' => 'User type not found'], 404);
        }
        
        $res->json($userType);
    }

    public static function store($req, $res) {
        $data = $req->body;
        
        // Validate required fields
        if (!isset($data['user_type'])) {
            return $res->json(['error' => 'User type is required'], 400);
        }

        // Check if user type already exists
        if (UserType::findByType($data['user_type'])) {
            return $res->json(['error' => 'User type already exists'], 409);
        }

        // Validate status if provided
        if (isset($data['status']) && !in_array($data['status'], ['active', 'inactive'])) {
            return $res->json(['error' => 'Status must be either active or inactive'], 400);
        }

        try {
            $id = UserType::create($data);
            $userType = UserType::find($id);
            $res->json($userType, 201);
        } catch (Exception $e) {
            $res->json(['error' => 'Failed to create user type'], 500);
        }
    }

    public static function update($req, $res) {
        $id = $req->params[0];
        $data = $req->body;
        
        // Check if user type exists
        $existingUserType = UserType::find($id);
        if (!$existingUserType) {
            return $res->json(['error' => 'User type not found'], 404);
        }

        // Validate status if provided
        if (isset($data['status']) && !in_array($data['status'], ['active', 'inactive'])) {
            return $res->json(['error' => 'Status must be either active or inactive'], 400);
        }

        // Check if user type name is being changed and if it already exists
        if (isset($data['user_type']) && $data['user_type'] !== $existingUserType['user_type']) {
            if (UserType::findByType($data['user_type'])) {
                return $res->json(['error' => 'User type already exists'], 409);
            }
        }

        try {
            $success = UserType::update($id, $data);
            if ($success) {
                $userType = UserType::find($id);
                $res->json($userType);
            } else {
                $res->json(['error' => 'No changes made'], 400);
            }
        } catch (Exception $e) {
            $res->json(['error' => 'Failed to update user type'], 500);
        }
    }

    public static function delete($req, $res) {
        $id = $req->params[0];
        
        // Check if user type exists
        if (!UserType::find($id)) {
            return $res->json(['error' => 'User type not found'], 404);
        }

        try {
            $success = UserType::delete($id);
            if ($success) {
                $res->json(['message' => 'User type deleted successfully']);
            } else {
                $res->json(['error' => 'Failed to delete user type'], 500);
            }
        } catch (Exception $e) {
            $res->json(['error' => 'Failed to delete user type'], 500);
        }
    }

    public static function getActive($req, $res) {
        $activeUserTypes = UserType::getActiveTypes();
        $res->json($activeUserTypes);
    }
}
