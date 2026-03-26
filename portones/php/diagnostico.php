<?php
header("Content-Type: application/json");

// Atrapar TODO incluyendo errores fatales
set_error_handler(function ($errno, $errstr) {
    echo json_encode([
    'crash' => true,
    'errno' => $errno,
    'error' => $errstr
    ]);
    exit();
});

try {
    $host = "127.0.0.1";
    $usuario = "isma";
    $password = "camarena";
    $base_datos = "control_escolar";
    $pem = '/var/www/proyects/api/global-bundle.pem';

    $conn = mysqli_init();
    mysqli_ssl_set($conn, NULL, NULL, $pem, NULL, NULL);

    $conectado = @mysqli_real_connect(
        $conn, $host, $usuario, $password, $base_datos, 3306,
        MYSQLI_CLIENT_SSL
    );

    echo json_encode([
        'conectado' => $conectado,
        'error' => mysqli_connect_error(),
        'errno' => mysqli_connect_errno(),
    ]);

}
catch (Throwable $e) {
    echo json_encode([
        'crash' => true,
        'mensaje' => $e->getMessage(),
        'tipo' => get_class($e)
    ]);
}