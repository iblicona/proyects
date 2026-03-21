<?php
header("Content-Type: application/json");

$host = "production.ccjgeakiwlqp.us-east-1.rds.amazonaws.com";
$user = "luisfer";
$pass = "copcal";
$db   = "itla_rentas";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo json_encode(["error" => "Error de conexión"]);
    exit;
}

$conn->set_charset("utf8");
?>