
<?php
use app\controllers\UserController;
use app\controllers\DbSyncController;
use app\controllers\UserTypeController;
use app\controllers\ServiceController;

$router->get('/api/services', [ServiceController::class, 'index']);
$router->get('/api/services/count', [ServiceController::class, 'count']);
$router->get('/api/services/{id}', [ServiceController::class, 'show']);
$router->post('/api/services', [ServiceController::class, 'store']);
$router->put('/api/services/{id}', [ServiceController::class, 'update']);
$router->delete('/api/services/{id}', [ServiceController::class, 'delete']);

$router->post('/api/register', [UserController::class, 'register']);
$router->post('/api/login', [UserController::class, 'login']);
$router->get('/api/users', [UserController::class, 'index']);
$router->get('/api/users/{id}', [UserController::class, 'show']);
$router->put('/api/users/{id}', [UserController::class, 'update']);
$router->delete('/api/users/{id}', [UserController::class, 'delete']);

// Contact Forms CRUD
$router->get('/api/contact-forms', [\app\controllers\ContactFormController::class, 'index']);
$router->get('/api/contact-forms/{id}', [\app\controllers\ContactFormController::class, 'show']);
$router->post('/api/contact-forms', [\app\controllers\ContactFormController::class, 'store']);
$router->put('/api/contact-forms/{id}', [\app\controllers\ContactFormController::class, 'update']);
$router->delete('/api/contact-forms/{id}', [\app\controllers\ContactFormController::class, 'delete']);
$router->post('/api/contact-forms/{id}/status', [\app\controllers\ContactFormController::class, 'changeStatus']);

// User Types CRUD routes
$router->get('/api/user-types', [UserTypeController::class, 'index']);
$router->get('/api/user-types/active', [UserTypeController::class, 'getActive']);
$router->get('/api/user-types/{id}', [UserTypeController::class, 'show']);
$router->post('/api/user-types', [UserTypeController::class, 'store']);
$router->put('/api/user-types/{id}', [UserTypeController::class, 'update']);
$router->delete('/api/user-types/{id}', [UserTypeController::class, 'delete']);

$router->post('/api/sync-db', [DbSyncController::class, 'sync']);
$router->get('/api/ping', function() {
    header('Content-Type: application/json');
    echo json_encode(['message' => 'pong', 'status' => 'ok']);
});
$router->post('/api/verify-otp', [UserController::class, 'verifyOtp']);
$router->post('/api/resend-otp', [UserController::class, 'resendOtp']);
$router->post('/api/reset-password', [UserController::class, 'resetPassword']);

