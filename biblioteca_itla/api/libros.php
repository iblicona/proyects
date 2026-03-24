<?php
header("Content-Type: application/json");
include("conexion.php");

$result = $conn->query("SELECT * FROM libros");

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
?>