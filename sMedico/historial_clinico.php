<?php
include ("php/pruebaConexion.php");
if($conn) {
    $consulta = "SELECT * FROM Alumnos";
    $resultado = mysqli_query($conn, $consulta);   
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Búsqueda de Historial Médico</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">"
  <link href="css/smcss.css" rel="stylesheet">
</head>
<body>
    <div class="med-container-fluid">
        <span>Historial Médico</span>
        <a href="opciones.html">Volver al Menú Principal</a>
    </div>
    <div class="h-container">
        <h2>Búsqueda de Historial</h2>

        <label for="nombre">Nombre del paciente:</label>
        <input type="text" id="nombre" name="nombre">

        <button id="search-button">Buscar</button>
        <a href="registro_alumno.html" class="add">Agregar Nuevo</a>
    </div>
    <div id="resultado"></div>
    <table id="inventory-table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Matrícula</th>
                <th>Padecimientos</th>
                <th>contacto de emergencia</th>
            </tr>
        </thead>
        <tbody id="inventory-tbody"></tbody>
         <?php
        if ($resultado) {
            $num_rows = mysqli_num_rows($resultado);
            if ($num_rows > 0) {
                while ($fila = mysqli_fetch_assoc($resultado)) {
                    echo "<tr>";
                    echo "<td>".$fila['id_alumno']."</td>";
                    echo "<td>".$fila['nombre']."</td>";
                    echo "<td>".$fila['matricula']."</td>";
                    echo "<td>".$fila['padecimientos']."</td>";
                    echo "<td>".$fila['contacto_emergencia']."</td>";
                    echo "<td><a href='php/editar.php?id=".$fila['id_alumno']."' class='btn btn-sm btn-primary'>Editar</a></td>";
                    echo "<td><a href='php/eliminar.php?id=".$fila['id_alumno']."' class='btn btn-sm btn-danger' onclick='return confirm(\"¿Estás seguro de que quieres eliminar este registro?\");'>Eliminar</a></td>";
                    echo "</tr>";   
                }
            } else {
                echo "<tr><td colspan='8'>No se encontraron registros.</td></tr>";
            }
        } else {
            echo "<tr><td colspan='8'>Error en la consulta.</td></tr>";
        }
        ?>
  </table>
</body>
</html>