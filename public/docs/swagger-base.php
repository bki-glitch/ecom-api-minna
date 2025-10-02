<?php
// Reads .env and outputs swagger-base.json with dynamic server url
$env = file_exists(__DIR__ . '/../../.env') ? file(__DIR__ . '/../../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
$data = [];
foreach ($env as $line) {
    if (strpos(trim($line), '=') !== false) {
        list($key, $value) = explode('=', $line, 2);
        $data[trim($key)] = trim($value);
    }
}
$server = rtrim($data['SERVER'] ?? 'http://localhost', '/');
$folder = '/' . trim($data['FOLDER_PATH'] ?? '', '/');
$baseUrl = $server . $folder;
$swagger = [
    "openapi" => "3.0.0",
    "info" => [
        "title" => "PHP API Boilerplate",
        "version" => "1.0.0"
    ],
    "servers" => [
        ["url" => $baseUrl]
    ],
    "paths" => new stdClass(),
    "components" => new stdClass()
];

// At the end, do not output or set header, just:
return $swagger;
