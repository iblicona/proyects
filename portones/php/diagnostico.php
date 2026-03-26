<?php
// diagnostico.php — BORRAR DESPUÉS
header("Content-Type: application/json");

$ruta = '../../api/dbconection.php';

echo json_encode([
    'ruta_resuelta' => realpath($ruta), // Ruta absoluta real
    'archivo_existe' => file_exists($ruta), // true/false
    'error_conexion' => '',
    'prueba_mysqli' => ''
]);

// Solo si el archivo existe, intenta conectar
if (file_exists($ruta)) {
    $conn = @mysqli_connect("127.0.0.1", "isma", "camarena", "control_escolar");
    if ($conn) {
        $data['prueba_mysqli'] = 'Conexión directa OK';
        $conn->close();
    }
    else {
        $data['error_conexion'] = mysqli_connect_error();
    }
}
