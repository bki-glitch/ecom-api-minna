<?php
// Serve Swagger JSON for docs
header('Content-Type: application/json');
readfile(__DIR__ . '/../docs/swagger.json');

// Ensure all 'use' statements use lowercase namespaces if present
