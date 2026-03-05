# Biblioteca ITLA – Instrucciones de instalación

## Requisitos
- PHP 8.1 o superior
- MySQL 5.7+ / MariaDB 10.3+
- Servidor local: XAMPP, Laragon, WAMP, o cualquier servidor con PHP

---

## Estructura del proyecto

```
biblioteca/
├── index.php          ← Login (reemplaza inicio.html)
├── registro.php       ← Registro de usuarios
├── admin.php          ← Panel de administración
├── exportar.php       ← Descarga de Excel por mes
├── setup.sql          ← Script de base de datos (ejecutar UNA VEZ)
│
├── includes/
│   ├── db.php         ← Conexión PDO a MySQL
│   └── auth.php       ← Manejo de sesiones y login
│
├── css/
│   ├── estilo.css     ← Estilos del login
│   ├── registro.css   ← Estilos del registro
│   └── admin.css      ← Estilos del panel admin
│
├── js/
│   └── registro.js    ← Validación correo + chatbot IA
│
└── img/
    ├── fondo.jpg      ← Tu imagen de fondo (agregar manualmente)
    ├── logo2.png      ← Tu logo ITLA (agregar manualmente)
    ├── mensaje.mp3    ← Sonido del chat (agregar manualmente)
    └── fotos/         ← Carpeta creada automáticamente para fotos
```

---

## Pasos de instalación

### 1. Crear la base de datos
Abre tu gestor MySQL (phpMyAdmin, terminal, TablePlus, etc.) y ejecuta:

```bash
mysql -u root -p < setup.sql
```

O copia y pega el contenido de `setup.sql` en phpMyAdmin → pestaña SQL.

### 2. Configurar la conexión
Abre `includes/db.php` y ajusta tus credenciales:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');    // tu usuario MySQL
define('DB_PASS', '');        // tu contraseña MySQL
define('DB_NAME', 'biblioteca_itla');
```

### 3. Copiar la carpeta al servidor
- **XAMPP**: copia `biblioteca/` dentro de `C:/xampp/htdocs/`
- **Laragon**: copia dentro de `C:/laragon/www/`

### 4. Acceder desde el navegador
```
http://localhost/biblioteca/index.php
```

---

## Credenciales por defecto

| Campo    | Valor   |
|----------|---------|
| Usuario  | admin   |
| Contraseña | 12345 |

> ⚠️ **Cambia la contraseña en producción.** Para hacerlo, genera un hash nuevo:
> ```php
> echo password_hash('tu_nueva_clave', PASSWORD_BCRYPT);
> ```
> Y actualiza la tabla `admins` con ese hash.

---

## Diferencias con la versión anterior (localStorage)

| Antes (JS + localStorage) | Ahora (PHP + MySQL) |
|--------------------------|---------------------|
| Datos solo en el navegador | Datos en servidor real |
| Login sin seguridad real | Login con sesiones PHP seguras |
| No hay imágenes reales | Fotos subidas al servidor |
| Exportar desde JS | Exportar desde servidor (PHP) |
| Sin validación de servidor | Validación doble (JS + PHP) |
