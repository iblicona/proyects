<?php
include("pruebaConexion.php");

// ── Guardar cambios (POST) ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_alumnos'])) {
    $id                   = intval($_POST['id_alumnos']);
    $matricula            = mysqli_real_escape_string($conn, $_POST['matricula']);
    $nombre               = mysqli_real_escape_string($conn, $_POST['nombre_alumno']);
    $apellido_paterno     = mysqli_real_escape_string($conn, $_POST['apellido_paterno']);
    $apellido_materno     = mysqli_real_escape_string($conn, $_POST['apellido_materno']);
    $genero               = mysqli_real_escape_string($conn, $_POST['genero']);
    $correo               = mysqli_real_escape_string($conn, $_POST['correo']);
    $telefono             = mysqli_real_escape_string($conn, $_POST['telefono']);
    $carrera              = mysqli_real_escape_string($conn, $_POST['carrera']);
    $fecha_nacimiento     = mysqli_real_escape_string($conn, $_POST['fecha_nacimiento']);
    $direccion            = mysqli_real_escape_string($conn, $_POST['direccion']);
    $contacto_emergencia  = mysqli_real_escape_string($conn, $_POST['contacto_emergencia']);
    $alergias             = mysqli_real_escape_string($conn, $_POST['alergias']);
    $enfermedades_cronicas= mysqli_real_escape_string($conn, $_POST['enfermedades_cronicas']);

    $sql = "UPDATE Alumnos SET
                matricula             = '$matricula',
                nombre_alumno         = '$nombre',
                apellido_paterno      = '$apellido_paterno',
                apellido_materno      = '$apellido_materno',
                genero                = '$genero',
                correo                = '$correo',
                telefono              = '$telefono',
                carrera               = '$carrera',
                fecha_nacimiento      = '$fecha_nacimiento',
                direccion             = '$direccion',
                contacto_emergencia   = '$contacto_emergencia',
                alergias              = '$alergias',
                enfermedades_cronicas = '$enfermedades_cronicas'
            WHERE id_alumnos = $id";

    if (mysqli_query($conn, $sql)) {
        header("Location: ../historial_clinico.php?msg=actualizado");
        exit();
    } else {
        $errorMsg = "Error al actualizar: " . mysqli_error($conn);
    }
}

// ── Cargar datos del registro (GET) ───────────────────────────────────────
$alumno = null;
if (isset($_GET['id'])) {
    $id  = intval($_GET['id']);
    $res = mysqli_query($conn, "SELECT * FROM Alumnos WHERE id_alumnos = $id");
    if ($res) {
        $alumno = mysqli_fetch_assoc($res);
    }
}

if (!$alumno) {
    header("Location: ../historial_clinico.php?error=notfound");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Historial - <?= htmlspecialchars($alumno['nombre_alumno']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/smcss.css" rel="stylesheet">
</head>
<body>
    <div class="register-container-fluid">
        <span>Editar Historial Clínico</span>
        <a href="../historial_clinico.php">Volver al Historial</a>
    </div>
    <div class="login-container">
        <h2>Editar Registro</h2><br>

        <?php if (!empty($errorMsg)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errorMsg) ?></div>
        <?php endif; ?>

        <form method="POST" action="editarh.php">
            <input type="hidden" name="id_alumnos" value="<?= $alumno['id_alumnos'] ?>">

            <label for="nombre_alumno">Nombre(s):</label>
            <input type="text" id="nombre_alumno" name="nombre_alumno"
                   value="<?= htmlspecialchars($alumno['nombre_alumno']) ?>" required><br><br>

            <label for="apellido_paterno">Apellido Paterno:</label>
            <input type="text" id="apellido_paterno" name="apellido_paterno"
                   value="<?= htmlspecialchars($alumno['apellido_paterno']) ?>" required><br><br>

            <label for="apellido_materno">Apellido Materno:</label>
            <input type="text" id="apellido_materno" name="apellido_materno"
                   value="<?= htmlspecialchars($alumno['apellido_materno']) ?>" required><br><br>

            <label for="matricula">Matrícula:</label>
            <input type="number" id="matricula" name="matricula"
                   value="<?= htmlspecialchars($alumno['matricula']) ?>" required><br><br>

            <label for="genero">Género:</label>
            <select id="genero" name="genero" required>
                <option value="">Selecciona un género</option>
                <option value="masculino" <?= $alumno['genero'] === 'masculino' ? 'selected' : '' ?>>Masculino</option>
                <option value="femenino"  <?= $alumno['genero'] === 'femenino'  ? 'selected' : '' ?>>Femenino</option>
                <option value="otro"      <?= $alumno['genero'] === 'otro'      ? 'selected' : '' ?>>Otro</option>
            </select><br><br>

            <label for="correo">Correo institucional:</label>
            <input type="email" id="correo" name="correo"
                   value="<?= htmlspecialchars($alumno['correo']) ?>" required><br><br>

            <label for="telefono">Teléfono celular:</label>
            <input type="tel" id="telefono" name="telefono"
                   value="<?= htmlspecialchars($alumno['telefono']) ?>" required><br><br>

            <label for="carrera">Escolaridad/Carrera:</label>
            <input type="text" id="carrera" name="carrera"
                   value="<?= htmlspecialchars($alumno['carrera']) ?>" required><br><br>

            <label for="fecha_nacimiento">Fecha de nacimiento:</label>
            <input type="date" id="fecha_nacimiento" name="fecha_nacimiento"
                   value="<?= htmlspecialchars($alumno['fecha_nacimiento']) ?>" required><br><br>

            <label for="direccion">Dirección:</label>
            <input type="text" id="direccion" name="direccion"
                   value="<?= htmlspecialchars($alumno['direccion']) ?>" required><br><br>

            <label for="contacto_emergencia">Contacto de emergencia:</label>
            <input type="tel" id="contacto_emergencia" name="contacto_emergencia"
                   value="<?= htmlspecialchars($alumno['contacto_emergencia']) ?>" required><br><br>

            <label for="alergias">Alergias:</label>
            <textarea id="alergias" name="alergias" required><?= htmlspecialchars($alumno['alergias']) ?></textarea><br><br>

            <label for="enfermedades_cronicas">Enfermedades crónicas/Tratamientos:</label>
            <textarea id="enfermedades_cronicas" name="enfermedades_cronicas" required><?= htmlspecialchars($alumno['enfermedades_cronicas']) ?></textarea><br><br>

            <div class="d-flex gap-2">
                <button type="submit" name="guardar" id="guardar">Guardar Cambios</button>
                <a href="../historial_clinico.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>