<?php
namespace app\controllers;

use app\models\ContactForm;

class ContactFormController {
    public static function sync($req, $res) {
        ContactForm::syncTable();
        $res->json(['message' => 'ContactForm table synced']);
    }

    public static function index($req, $res) {
        $items = ContactForm::all();
        $res->json($items);
    }

    public static function show($req, $res) {
        $id = $req->params[0];
        $item = ContactForm::find($id);
        if (!$item) return $res->json(['error' => 'Contact not found'], 404);
        $res->json($item);
    }

    public static function store($req, $res) {
        $data = $_POST;
        // Support JSON payloads (Swagger UI and many clients send application/json)
        if (empty($data)) {
            $raw = file_get_contents('php://input');
            $json = json_decode($raw, true);
            if (is_array($json)) $data = $json;
        }
        // Basic validation
        if (!isset($data['first_name'], $data['last_name'], $data['email'], $data['message'])) {
            return $res->json(['error' => 'Missing required fields'], 400);
        }
        if (isset($data['status']) && !in_array($data['status'], ['unread','read','replied'])) {
            return $res->json(['error' => 'Invalid status'], 400);
        }
        $id = ContactForm::create($data);
        $item = ContactForm::find($id);
        $res->json($item, 201);
    }

    public static function update($req, $res) {
        $id = $req->params[0];
        $data = $_POST;
        if (empty($data)) {
            $raw = file_get_contents('php://input');
            $json = json_decode($raw, true);
            if (is_array($json)) $data = $json;
        }
        $item = ContactForm::find($id);
        if (!$item) return $res->json(['error' => 'Contact not found'], 404);
        if (isset($data['status']) && !in_array($data['status'], ['unread','read','replied'])) {
            return $res->json(['error' => 'Invalid status'], 400);
        }
        $ok = ContactForm::update($id, $data);
        $item = ContactForm::find($id);
        $res->json($item ?: ['error' => 'Update failed']);
    }

    public static function delete($req, $res) {
        $id = $req->params[0];
        $item = ContactForm::find($id);
        if (!$item) return $res->json(['error' => 'Contact not found'], 404);
        $ok = ContactForm::delete($id);
        $res->json(['success' => $ok]);
    }

    // Optional helper to change status via a dedicated endpoint
    public static function changeStatus($req, $res) {
        $id = $req->params[0];
        $data = $_POST;
        if (empty($data)) {
            $raw = file_get_contents('php://input');
            $json = json_decode($raw, true);
            if (is_array($json)) $data = $json;
        }
        if (!isset($data['status']) || !in_array($data['status'], ['unread','read','replied'])) {
            return $res->json(['error' => 'Invalid status'], 400);
        }
        $item = ContactForm::find($id);
        if (!$item) return $res->json(['error' => 'Contact not found'], 404);
        $ok = ContactForm::update($id, ['status' => $data['status']]);
        $res->json(['success' => $ok]);
    }
}
