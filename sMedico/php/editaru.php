<?php
include("pruebaConexion.php");

// ── ACTUALIZAR (POST) ───────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_utensilio'])) {

    $id        = intval($_POST['id_utensilio']);
    $nombre    = mysqli_real_escape_string($conn, $_POST['nombre']);
    $tipo      = mysqli_real_escape_string($conn, $_POST['tipo']);
    $cantidad  = intval($_POST['cantidad']);

    $sql = "UPDATE Utensilios SET
                nombre = '$nombre',
                `Tipo_material` = '$tipo',
                cantidad = $cantidad
            WHERE id_utensilio = $id";

    if (mysqli_query($conn, $sql)) {
        header("Location: ../material_clinico.php?msg=actualizado");
        exit();
    } else {
        $errorMsg = "Error al actualizar: " . mysqli_error($conn);
    }
}

// ── CARGAR DATOS (GET) ───────────────────────────────
$utensilio = null;

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $res = mysqli_query($conn, "SELECT * FROM Utensilios WHERE id_utensilio = $id");

    if ($res) {
        $utensilio = mysqli_fetch_assoc($res);
    }
}

// Si no existe, regresar
if (!$utensilio) {
    header("Location: ../material_clinico.php?error=notfound");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Material</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/smcss.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">
    <h2>Editar Material</h2>

    <?php if (!empty($errorMsg)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($errorMsg) ?></div>
    <?php endif; ?>

    <form method="POST" action="editaru.php">

        <input type="hidden" name="id_utensilio" value="<?= $utensilio['id_utensilio'] ?>">

        <label>Nombre:</label>
        <input type="text" name="nombre"
            value="<?= htmlspecialchars($utensilio['nombre']) ?>" required class="form-control"><br>

        <label>Tipo:</label>
        <select name="tipo" class="form-control" required>
            <option value="des" <?= $utensilio['Tipo_material'] === 'des' ? 'selected' : '' ?>>Desechable</option>
            <option value="nodes" <?= $utensilio['Tipo_material'] === 'nodes' ? 'selected' : '' ?>>No desechable</option>
        </select><br>

        <label>Cantidad:</label>
        <input type="number" name="cantidad"
            value="<?= $utensilio['cantidad'] ?>" required class="form-control"><br>

        <button type="submit" class="btn btn-success">Guardar Cambios</button>
        <a href="../material_clinico.php" class="btn btn-secondary">Cancelar</a>

    </form>
</div>

</body>
</html>