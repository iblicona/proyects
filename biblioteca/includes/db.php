<?php
include("/var/www/projects/api/dbconnection.php");

function getDB(): PDO {
    static $pdo = null;
    
    if ($pdo === null) {
        global $host, $usuario, $password, $base_datos;

        try {
            $pdo = new PDO(
                "mysql:host=" . $host . ";dbname=" . $base_datos . ";charset=utf8mb4",
                $usuario,
                $password,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['error' => 'Error de conexión: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}
?>
