 <?php
include("pruebaConexion.php");

if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // intval previene SQL injection
    $eliminar = "DELETE FROM Alumnos WHERE id_alumnos = $id";
    $resultado = mysqli_query($conn, $eliminar);

    if ($resultado) {
        header("Location: ../historial_clinico.php?msg=eliminado");
    } else {
        header("Location: ../historial_clinico.php?error=1");
    }
    exit(); // IMPORTANTE: detener ejecución tras el redirect
}
?>