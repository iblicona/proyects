<?php
header("Content-Type: application/json");

$host = "127.0.0.1";
$usuario = "isma";
$password = "camarena";
$base_datos = "control_escolar";
$pem = '/var/www/proyects/api/global-bundle.pem';

$conn = mysqli_init();
mysqli_ssl_set($conn, NULL, NULL, $pem, NULL, NULL);

$conectado = mysqli_real_connect($conn, $host, $usuario, $password, $base_datos, 3306);

echo json_encode([
    'conectado' => $conectado,
    'error' => mysqli_connect_error(),
    'errno' => mysqli_connect_errno(),
]);