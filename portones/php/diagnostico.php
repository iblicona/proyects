<?php
header("Content-Type: application/json");

$contenido = file_get_contents('../../api/dbconection.php');

echo json_encode([
    'primeras_lineas' => substr($contenido, 0, 500)
]);