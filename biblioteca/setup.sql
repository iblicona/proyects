-- ============================================================
--  biblioteca_itla  –  Script de creación
--  Ejecuta este archivo una sola vez en tu servidor MySQL/MariaDB
--  Ejemplo: mysql -u root -p < setup.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS biblioteca_itla
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE biblioteca_itla;

-- ── Tabla de administradores ──────────────────────────────────
CREATE TABLE IF NOT EXISTS admins (
    id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario  VARCHAR(80)  NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL          -- almacena el hash bcrypt
) ENGINE=InnoDB;

-- Admin por defecto: usuario=admin  contraseña=12345
-- El hash se genera con password_hash('12345', PASSWORD_BCRYPT)
INSERT IGNORE INTO admins (usuario, password)
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
-- ↑ Hash de ejemplo para '12345'. Cámbialo en producción.

-- ── Tabla de usuarios de biblioteca ──────────────────────────
CREATE TABLE IF NOT EXISTS usuarios (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre     VARCHAR(150) NOT NULL,
    matricula  VARCHAR(50)  NOT NULL,
    tipo       ENUM('Alumno','Docente','Administrativo') NOT NULL,
    area       VARCHAR(150) NOT NULL,
    correo     VARCHAR(150) NOT NULL,
    telefono   VARCHAR(20)  NOT NULL,
    foto       VARCHAR(255) DEFAULT NULL,   -- nombre del archivo guardado
    fecha      DATE         NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
