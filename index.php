<?php
// Simple PHP API entry point with RMC architecture
require_once __DIR__ . '/config/bootstrap.php';

use app\core\Router;

$router = new Router();
require_once __DIR__ . '/routes/api.php';
$router->dispatch();
