<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include("conexion.php");

// 🔍 Validar datos requeridos
$required = ['nombre','matricula','tipo','correo'];

foreach ($required as $field) {
    if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
        echo json_encode([
            "status" => "error",
            "msg" => "Falta el campo: $field"
        ]);
        exit;
    }
}

// 🔒 Sanitizar datos
$nombre = $conn->real_escape_string($_POST['nombre']);
$matricula = $conn->real_escape_string($_POST['matricula']);
$tipo = $conn->real_escape_string($_POST['tipo']);
$area = $conn->real_escape_string($_POST['area'] ?? '');
$correo = $conn->real_escape_string($_POST['correo']);
$telefono = $conn->real_escape_string($_POST['telefono'] ?? '');

// 🔍 Verificar duplicados
$check = $conn->query("SELECT id FROM usuarios 
                       WHERE matricula='$matricula' 
                       OR correo='$correo'");

if ($check && $check->num_rows > 0) {
    echo json_encode([
        "status" => "error",
        "msg" => "El usuario ya existe"
    ]);
    exit;
}

// 🖼️ Manejo de imagen
$fotoNombre = null;

if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {

    $permitidos = ['image/jpeg','image/png','image/jpg'];

    if (!in_array($_FILES['foto']['type'], $permitidos)) {
        echo json_encode([
            "status" => "error",
            "msg" => "Formato de imagen no permitido"
        ]);
        exit;
    }

    if (!is_dir("uploads")) {
        mkdir("uploads", 0777, true);
    }

    $fotoNombre = time() . "_" . basename($_FILES["foto"]["name"]);
    $ruta = "uploads/" . $fotoNombre;

    move_uploaded_file($_FILES["foto"]["tmp_name"], $ruta);
}

// 🔥 Insertar (SIN PASSWORD por ahora)
$sql = "INSERT INTO usuarios 
(nombre, matricula, tipo, area, correo, telefono, foto)
VALUES 
('$nombre','$matricula','$tipo','$area','$correo','$telefono','$fotoNombre')";

if ($conn->query($sql)) {
    echo json_encode([
        "status" => "ok",
        "msg" => "Usuario creado correctamente"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "msg" => "Error al guardar",
        "error" => $conn->error
    ]);
}
?>