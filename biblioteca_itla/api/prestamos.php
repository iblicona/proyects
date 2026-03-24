<?php
header("Content-Type: application/json");
include("conexion.php");

$sql = "SELECT p.*, u.nombre as usuario, l.titulo as libro
        FROM prestamos p
        JOIN usuarios u ON p.usuario_id = u.id
        JOIN libros l ON p.libro_id = l.id";

$result = $conn->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
?>