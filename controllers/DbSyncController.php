<?php
namespace app\controllers;

use app\models\User;
use app\models\UserType;
use app\models\Service;
use app\models\ContactForm;

class DbSyncController {
    public static function sync($req, $res) {
        // Call model sync methods in correct order
    UserType::syncTable();
    User::syncTable();
    Service::syncTable();
    ContactForm::syncTable();
        $res->json(['message' => 'Database synced']);
    }
}
