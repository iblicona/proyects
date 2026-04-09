 <?php
include("pruebaConexion.php");

if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // intval previene SQL injection
    $eliminar = "DELETE FROM Medicamentos WHERE id_medicamento = $id";
    $resultado = mysqli_query($conn, $eliminar);

    if ($resultado) {
        header("Location: ../medicamentos_inicio.php?msg=eliminado");
    } else {
        header("Location: ../medicamentos_inicio.php?error=1");
    }
    exit(); // IMPORTANTE: detener ejecución tras el redirect
}
?>