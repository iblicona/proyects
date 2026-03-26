<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include("conexion.php");

// Datos
$nombre = $_POST['nombre'];
$matricula = $_POST['matricula'];
$tipo = $_POST['tipo'];
$area = $_POST['area'];
$correo = $_POST['correo'];
$telefono = $_POST['telefono'];

// Imagen
$fotoNombre = null;

if (isset($_FILES['foto'])) {
    $foto = $_FILES['foto'];

    $fotoNombre = time() . "_" . basename($foto["name"]);
    $ruta = "uploads/" . $fotoNombre;

    move_uploaded_file($foto["tmp_name"], $ruta);
}

// Insertar
$sql = "INSERT INTO usuarios 
(nombre, matricula, tipo, area, correo, telefono, foto)
VALUES 
('$nombre','$matricula','$tipo','$area','$correo','$telefono','$fotoNombre')";

if ($conn->query($sql)) {
    echo json_encode(["status" => "ok"]);
} else {
    echo json_encode(["error" => $conn->error]);
}
?>