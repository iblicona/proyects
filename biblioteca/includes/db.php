<?php
include("/var/www/projects/api/dbconnection.php");

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $host       = "production.ccjgeakiwlqp.us-east-1.rds.amazonaws.com"; 
        $usuario    = "axel";        
        $password   = "admin1234";   
        $base_datos = "biblioteca";  

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
            die("Error de conexión: " . $e->getMessage()); 
        }
    }
    return $pdo;
}
?>
