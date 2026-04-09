 <?php
include("pruebaConexion.php");

if(isset($_GET['id'])) {
    $id = $_GET['id'];
    $eliminar = "DELETE FROM datos WHERE id = $id";
    $resultado = mysqli_query($conn, $eliminar);
    echo "Registro eliminado correctamente";
    header("Location: medicamentos.php");
}

?>