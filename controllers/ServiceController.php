<?php
namespace app\controllers;

use app\models\Service;

class ServiceController {
    public static function sync($req, $res) {
        Service::syncTable();
        $res->json(['message' => 'Service table synced']);
    }
    public static function index($req, $res) {
        $services = Service::all();
        $res->json($services);
    }
    public static function show($req, $res) {
        $id = $req->params[0];
        $service = Service::find($id);
        if (!$service) {
            return $res->json(['error' => 'Service not found'], 404);
        }
        $res->json($service);
    }
    public static function store($req, $res) {
        $data = $_POST;
        // Handle file upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../public/images/services/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $filename = uniqid('service_') . '_' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $data['image'] = 'public/images/services/' . $filename;
            } else {
                return $res->json(['error' => 'Image upload failed'], 500);
            }
        }
        if (!isset($data['service_name'])) {
            return $res->json(['error' => 'Service name is required'], 400);
        }
        if (isset($data['status']) && !in_array($data['status'], ['active', 'inactive'])) {
            return $res->json(['error' => 'Status must be either active or inactive'], 400);
        }
        $id = Service::create($data);
        $service = Service::find($id);
        $res->json($service, 201);
    }
    public static function update($req, $res) {
        $id = $req->params[0];
        $data = $_POST;
        $service = Service::find($id);
        if (!$service) {
            return $res->json(['error' => 'Service not found'], 404);
        }
        // Handle file upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../public/images/services/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $filename = uniqid('service_') . '_' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $data['image'] = 'public/images/services/' . $filename;
            } else {
                return $res->json(['error' => 'Image upload failed'], 500);
            }
        }
        if (isset($data['status']) && !in_array($data['status'], ['active', 'inactive'])) {
            return $res->json(['error' => 'Status must be either active or inactive'], 400);
        }
        $ok = Service::update($id, $data);
        $service = Service::find($id);
        $res->json($service);
    }
    public static function delete($req, $res) {
        $id = $req->params[0];
        $service = Service::find($id);
        if (!$service) {
            return $res->json(['error' => 'Service not found'], 404);
        }
        $ok = Service::delete($id);
        $res->json(['success' => $ok]);
    }
    public static function count($req, $res) {
        $count = Service::count();
        $res->json(['total' => $count]);
    }
}
