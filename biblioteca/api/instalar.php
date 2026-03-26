<?php
/* ================================
   INSTALADOR DE BASE DE DATOS
   Ejecuta este archivo UNA SOLA VEZ
================================ */

include("conexion.php");

// Conexión sin DB
$conn = new mysqli($host, $usuario, $password);

if ($conn->connect_error) {
    die("<h2 style='color:red'>Error de conexión: " . $conn->connect_error . "</h2>");
}

$sqls = [];

/* ================================
   CREAR BASE DE DATOS
================================ */
$sqls[] = "CREATE DATABASE IF NOT EXISTS $base_datos 
           CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";

$sqls[] = "USE $base_datos";

/* ================================
   TABLA USUARIOS
================================ */
$sqls[] = "CREATE TABLE IF NOT EXISTS usuarios (
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

/* ================================
   TABLA LIBROS
================================ */
$sqls[] = "CREATE TABLE IF NOT EXISTS libros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255),
    autor VARCHAR(255),
    categoria VARCHAR(100),
    stock INT DEFAULT 0,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

/* ================================
   ADMIN INICIAL
================================ */
$adminPass = password_hash("admin123", PASSWORD_BCRYPT);

$sqls[] = "INSERT IGNORE INTO usuarios 
(matricula, nombre, tipo, area, correo, telefono, password)
VALUES 
('ADMIN001', 'Administrador', 'admin', 'Sistema', 'admin@itla.com', '0000000000', '$adminPass')";

/* ================================
   EJECUCIÓN DE QUERYS
================================ */
echo "<h2 style='font-family:Arial'>Instalando sistema...</h2>";
echo "<ul style='font-family:Arial'>";

foreach ($sqls as $sql) {
    if ($conn->query($sql) === TRUE) {
        $preview = substr($sql, 0, 60) . "...";
        echo "<li style='color:green'>✅ OK: <code>$preview</code></li>";
    } else {
        echo "<li style='color:red'>❌ Error en: <code>" . substr($sql,0,60) . "</code><br>" . $conn->error . "</li>";
    }
}

echo "</ul>";

/* ================================
   MENSAJE FINAL
================================ */
echo "<h3 style='font-family:Arial;color:#0a2a5e'>✅ Instalación completa</h3>";
echo "<p style='font-family:Arial'>
<b>Usuario admin:</b><br>
Correo: admin@itla.com<br>
Password: admin123
</p>";

echo "<p style='font-family:Arial;color:red'>
<b>⚠️ Elimina o protege este archivo después de usarlo.</b>
</p>";

$conn->close();
?>