<?php
// diagnostico.php — BORRAR DESPUÉS
header("Content-Type: application/json");

echo json_encode([
    'php_version' => phpversion(),
    'mysqli_cargado' => extension_loaded('mysqli'),
    'extensiones' => get_loaded_extensions(),
    'archivo_db' => file_exists('../../api/dbconection.php'),
    'ruta_db' => realpath('../../api/dbconection.php'),
]);