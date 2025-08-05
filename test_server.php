<?php
// Test script to verify server setup
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

$response = array(
    'status' => 'success',
    'message' => 'Server is working correctly!',
    'method' => $_SERVER['REQUEST_METHOD'],
    'timestamp' => date('Y-m-d H:i:s'),
    'server_info' => array(
        'php_version' => phpversion(),
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'
    )
);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $response['post_data'] = $_POST;
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>