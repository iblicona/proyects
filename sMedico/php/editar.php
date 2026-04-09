<?php
include("pruebaConexion.php");

// ── Guardar cambios (POST) ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_medicamento'])) {
    $id              = intval($_POST['id_medicamento']);
    $nombre          = mysqli_real_escape_string($conn, $_POST['name']);
    $nombre_generico = mysqli_real_escape_string($conn, $_POST['generic-name']);
    $tipo_dosis      = mysqli_real_escape_string($conn, $_POST['dose']);
    $cantidad        = intval($_POST['quantity']);
    $presentacion    = intval($_POST['presentation']);

    $sql = "UPDATE Medicamentos
            SET Medicamento     = '$nombre',
                nombre_generico = '$nombre_generico',
                dosis           = '$tipo_dosis',
                Cantidad        = $cantidad,
                Presentacion    = $presentacion
            WHERE id_medicamento = $id";

    if (mysqli_query($conn, $sql)) {
        header("Location: ../medicamentos_inicio.php?msg=actualizado");
        exit();
    } else {
        $errorMsg = "Error al actualizar: " . mysqli_error($conn);
    }
}

// ── Cargar datos del registro (GET) ───────────────────────────────────────
$medicamento = null;
if (isset($_GET['id'])) {
    $id  = intval($_GET['id']);
    $res = mysqli_query($conn, "SELECT * FROM Medicamentos WHERE id_medicamento = $id");
    if ($res) {
        $medicamento = mysqli_fetch_assoc($res);
    }
}

if (!$medicamento) {
    header("Location: ../medicamentos_inicio.php?error=notfound");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Medicamento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/smcss.css" rel="stylesheet">
</head>
<body>
    <div class="med-container-fluid">
        <span>Editar Medicamento</span>
        <a href="../medicamentos_inicio.php">Volver al Inventario</a>
    </div>

    <div class="m-container">
        <h2>Editar Registro</h2><br>

        <?php if (!empty($errorMsg)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errorMsg) ?></div>
        <?php endif; ?>

        <form method="POST" action="editar.php">
            <input type="hidden" name="id_medicamento" value="<?= $medicamento['id_medicamento'] ?>">

            <div class="mb-3">
                <label for="name" class="form-label">Nombre comercial:</label>
                <input type="text" class="form-control" id="name" name="name"
                       value="<?= htmlspecialchars($medicamento['Medicamento']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="generic-name" class="form-label">Nombre genérico:</label>
                <input type="text" class="form-control" id="generic-name" name="generic-name"
                       value="<?= htmlspecialchars($medicamento['nombre_generico']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="dose" class="form-label">Tipo de Dosis:</label>
                <select class="form-select" id="dose" name="dose" required>
                    <option value="pastilla" <?= $medicamento['dosis'] === 'pastilla' ? 'selected' : '' ?>>Pastilla</option>
                    <option value="tableta"  <?= $medicamento['dosis'] === 'tableta'  ? 'selected' : '' ?>>Tableta</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="quantity" class="form-label">Cantidad:</label>
                <input type="number" class="form-control" id="quantity" name="quantity"
                       value="<?= intval($medicamento['Cantidad']) ?>" min="1" required>
            </div>

            <div class="mb-3">
                <label for="presentation" class="form-label">Presentación (mg):</label>
                <input type="number" class="form-control" id="presentation" name="presentation"
                       value="<?= intval($medicamento['Presentacion']) ?>" min="1" required>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                <a href="../medicamentos_inicio.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
