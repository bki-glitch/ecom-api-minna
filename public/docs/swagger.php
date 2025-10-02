<?php
// Instead of loading static swagger-base.json, include swagger-base.php for dynamic server url
$base = require __DIR__ . '/swagger-base.php';

$files = ['users.json', 'user-types.json', 'system.json', 'service.json', 'contact-forms.json'];
foreach ($files as $file) {
    $doc = json_decode(file_get_contents(__DIR__ . '/../../docs/' . $file), true);
    if (isset($doc['paths'])) {
        if (!isset($base['paths']) || !is_array($base['paths'])) $base['paths'] = [];
        $base['paths'] = array_merge($base['paths'], $doc['paths']);
    }
    if (isset($doc['components'])) {
        if (!isset($base['components']) || !is_array($base['components'])) $base['components'] = [];
        $base['components'] = array_merge_recursive($base['components'], $doc['components']);
    }
}
header('Content-Type: application/json');
echo json_encode($base);
