<?php
// Reads .env and outputs FOLDER_PATH as JSON for swagger-initializer.js
$env = file_exists(__DIR__ . '/../../.env') ? file(__DIR__ . '/../../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
$data = [];
foreach ($env as $line) {
    if (strpos(trim($line), '=') !== false) {
        list($key, $value) = explode('=', $line, 2);
        $data[trim($key)] = trim($value);
    }
}
header('Content-Type: application/json');
echo json_encode([
    'FOLDER_PATH' => $data['FOLDER_PATH']
]);
