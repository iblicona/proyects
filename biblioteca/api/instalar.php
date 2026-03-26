<?php
include("conexion.php"); // 🔐 ya trae SSL correcto

$sqls = [];

/* ================================
   USAR BASE (ya existe)
================================ */
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
   ADMIN
================================ */
$adminPass = password_hash("admin123", PASSWORD_BCRYPT);

$sqls[] = "INSERT IGNORE INTO usuarios 
(matricula, nombre, tipo, area, correo, telefono, password)
VALUES 
('ADMIN001', 'Administrador', 'admin', 'Sistema', 'admin@itla.com', '0000000000', '$adminPass')";

/* ================================
   EJECUCIÓN
================================ */
echo "<h2>Instalando sistema...</h2><ul>";

foreach ($sqls as $sql) {
    if ($conn->query($sql)) {
        echo "<li>✅ OK</li>";
    } else {
        echo "<li>❌ " . $conn->error . "</li>";
    }
}

echo "</ul>";
echo "<h3>✅ Instalación completa</h3>";
?>