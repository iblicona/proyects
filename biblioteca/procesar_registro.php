<?php
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre    = trim($_POST['nombre']    ?? '');
    $matricula = trim($_POST['matricula'] ?? '');
    $tipo      = $_POST['tipo']           ?? '';
    $area      = trim($_POST['area']      ?? '');
    $correo    = strtolower(trim($_POST['correo'] ?? ''));
    $telefono  = trim($_POST['telefono']  ?? '');

    $errores = [];

    if ($nombre === '')    $errores[] = 'El nombre es obligatorio.';
    if ($matricula === '') $errores[] = 'La matrícula es obligatoria.';
    if (!in_array($tipo, ['Alumno','Docente','Administrativo'])) $errores[] = 'Tipo de usuario inválido.';
    if ($area === '')      $errores[] = 'La carrera/área es obligatoria.';
    if (!str_ends_with($correo, '@itla.edu.mx')) $errores[] = 'El correo debe terminar en @itla.edu.mx.';
    if ($telefono === '')  $errores[] = 'El teléfono es obligatorio.';

    $fotoNombre = null;
    if (!empty($_FILES['foto']['name'])) {
        $ext       = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $permitidos = ['jpg','jpeg','png','gif','webp'];
        if (!in_array($ext, $permitidos)) {
            $errores[] = 'Formato de imagen no permitido.';
        } else {
            $fotoNombre = uniqid('foto_', true) . '.' . $ext;
            $destino    = __DIR__ . '/img/fotos/' . $fotoNombre;
            if (!is_dir(__DIR__ . '/img/fotos')) {
                mkdir(__DIR__ . '/img/fotos', 0755, true);
            }
            if (!move_uploaded_file($_FILES['foto']['tmp_name'], $destino)) {
                $errores[] = 'Error al subir la fotografía.';
                $fotoNombre = null;
            }
        }
    }

    // bien
    if (empty($errores)) {
        try {
            $pdo  = getDB();
            $stmt = $pdo->prepare(
                "INSERT INTO usuarios (nombre, matricula, tipo, area, correo, telefono, foto, fecha)
                 VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE())"
            );
            $stmt->execute([$nombre, $matricula, $tipo, $area, $correo, $telefono, $fotoNombre]);

            $mensaje = urlencode('🎉 Usuario registrado correctamente. ¡Ya puedes solicitar libros en la biblioteca!');
            header("Location: registro.html?tipo=exito&mensaje=$mensaje");
            exit;
        } catch (PDOException $e) {
            $mensaje = urlencode('Error al guardar: ' . $e->getMessage());
            header("Location: registro.html?tipo=error&mensaje=$mensaje");
            exit;
        }
    } else {
        // Si hubo errores de validación, los unimos con un separador y regresamos
        $mensaje = urlencode(implode(' | ', $errores));
        header("Location: registro.html?tipo=error&mensaje=$mensaje");
        exit;
    }
} else {
    // Si entran directo al PHP sin post
    header('Location: registro.html');
    exit;
}
?>