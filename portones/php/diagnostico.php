<?php
header("Content-Type: application/json");

echo json_encode([
    'pem_en_api' => file_exists('../../api/global-bundle.pem'),
    'ruta_pem' => realpath('../../api/global-bundle.pem'),
    'dir_actual' => __DIR__,
    'test_conn' => '',
    'error_conn' => '',
]);