<?php
// diagnostico.php — BORRAR DESPUÉS
header("Content-Type: application/json");

$resultado = [
    'ruta_resuelta' => realpath('../../api/dbconection.php'),
    'archivo_existe' => file_exists('../../api/dbconection.php'),
    'prueba_mysqli' => '',
    'error_conexion' => ''
];

// Prueba conexión directa
$conn = @mysqli_connect("127.0.0.1", "isma", "camarena", "control_escolar");

if ($conn) {
    $resultado['prueba_mysqli'] = 'Conexión directa OK';
    $conn->close();
}
else {
    $resultado['prueba_mysqli'] = 'FALLÓ';
    $resultado['error_conexion'] = mysqli_connect_error();
    $resultado['errno'] = mysqli_connect_errno();
}

echo json_encode($resultado);