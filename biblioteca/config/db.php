<?php
// 🔐 CONFIGURACIÓN DE PRODUCCIÓN (AWS RDS)
$host = "production.ccjgeakiwlqp.us-east-1.rds.amazonaws.com";
$usuario = "axel";
$password = "admin1234";
$base_datos   = "library";

// 🔗 Tu inclusión especial de SSL/Configuración de servidor
include("/var/www/proyects/api/dbconection.php");

try {
    // Usamos PDO para que sea compatible con todo el api.php que hicimos
    $pdo = new PDO("mysql:host=$host;dbname=$base_datos;charset=utf8mb4", $usuario, $password);
    
    // Configuración de errores para desarrollo/producción
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    header('Content-Type: application/json');
    die(json_encode(["error" => "Error de conexión al servidor: " . $e->getMessage()]));
}
?>