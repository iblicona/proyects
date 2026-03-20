<?php

define('DB_HOST', 'production.ccjgeakiwlqp.us-east-1.rds.amazonaws.com');
define('DB_USER', 'luisfer');
define('DB_PASS', 'copcal');
define('DB_NAME', 'itla_rentas');

function conectar() {

    $ssl_cert = __DIR__ . '/ServerDB.pem';

    $conn = mysqli_init();

    mysqli_ssl_set($conn, NULL, NULL, $ssl_cert, NULL, NULL);

    if (!mysqli_real_connect($conn, DB_HOST, DB_USER, DB_PASS, DB_NAME)) {
        http_response_code(500);
        echo json_encode([
            "error" => "Error de conexión: " . mysqli_connect_error()
        ]);
        exit();
    }

    $conn->set_charset("utf8");

    return $conn;
}

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}