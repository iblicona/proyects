<?php
include("conexion.php");

// Conexión sin DB
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Crear base de datos
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " 
        CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";

if (!$conn->query($sql)) {
    die("Error creando BD: " . $conn->error);
}

$conn->select_db(DB_NAME);


// =========================
// TABLA USUARIOS (AJUSTADA)
// =========================
$sql = "CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    foto VARCHAR(255),
    nombre VARCHAR(150) NOT NULL,
    matricula VARCHAR(50) UNIQUE,
    tipo VARCHAR(50),
    area VARCHAR(100),
    correo VARCHAR(150),
    telefono VARCHAR(20),
    password VARCHAR(255),
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!$conn->query($sql)) {
    die("Error creando tabla usuarios: " . $conn->error);
}


// =========================
// TABLA LIBROS (para menú)
// =========================
$sql = "CREATE TABLE IF NOT EXISTS libros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255),
    autor VARCHAR(255),
    categoria VARCHAR(100),
    stock INT DEFAULT 0,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$conn->query($sql);


// =========================
// ADMIN INICIAL
// =========================
$adminPass = password_hash("admin123", PASSWORD_BCRYPT);

$sql = "INSERT IGNORE INTO usuarios 
(matricula, nombre, tipo, area, correo, telefono, password)
VALUES 
('ADMIN001', 'Administrador', 'admin', 'Sistema', 'admin@itla.com', '0000000000', '$adminPass')";

$conn->query($sql);


// =========================
// MENSAJE FINAL
// =========================
echo "<h2>✅ Instalación completa</h2>";
echo "Usuario admin:<br>";
echo "Correo: admin@itla.com<br>";
echo "Password: admin123<br>";

?>