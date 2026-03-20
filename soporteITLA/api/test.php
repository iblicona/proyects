<?php
require 'conexion.php';

$conn = conectar();

echo json_encode(["mensaje" => "Conectado correctamente"]);