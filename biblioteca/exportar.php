<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
requireLogin();

$mes    = (int)($_GET['mes']  ?? date('n'));
$anio   = (int)($_GET['anio'] ?? date('Y'));
$filtro = trim($_GET['q'] ?? '');

$meses = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio',
          'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
$etiquetaMes = $meses[$mes] . ' ' . $anio;

$pdo = getDB();

$sql = "SELECT nombre, matricula, tipo, area, correo, telefono, fecha
        FROM usuarios
        WHERE MONTH(fecha) = :mes AND YEAR(fecha) = :anio";
$params = ['mes' => $mes, 'anio' => $anio];

if ($filtro !== '') {
    $sql .= " AND (nombre LIKE :q OR matricula LIKE :q OR correo LIKE :q)";
    $params['q'] = "%$filtro%";
}

$sql .= " ORDER BY fecha DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$usuarios = $stmt->fetchAll();

if (empty($usuarios)) {
    die('No hay usuarios para exportar en este mes.');
}

// Cabeceras para descarga de Excel
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="usuarios_' . $mes . '_' . $anio . '.xls"');
header('Cache-Control: max-age=0');

echo "\xEF\xBB\xBF"; // BOM UTF-8 para que Excel muestre tildes correctamente
?>
<table border="1">
  <tr>
    <th colspan="7" style="font-size:18px;">
      Usuarios del mes: <?= htmlspecialchars($etiquetaMes) ?>
    </th>
  </tr>
  <tr>
    <th>Nombre</th>
    <th>Matrícula</th>
    <th>Tipo</th>
    <th>Área</th>
    <th>Correo</th>
    <th>Teléfono</th>
    <th>Fecha</th>
  </tr>
  <?php foreach ($usuarios as $u): ?>
  <tr>
    <td><?= htmlspecialchars($u['nombre']) ?></td>
    <td><?= htmlspecialchars($u['matricula']) ?></td>
    <td><?= htmlspecialchars($u['tipo']) ?></td>
    <td><?= htmlspecialchars($u['area']) ?></td>
    <td><?= htmlspecialchars($u['correo']) ?></td>
    <td><?= htmlspecialchars($u['telefono']) ?></td>
    <td><?= htmlspecialchars($u['fecha']) ?></td>
  </tr>
  <?php endforeach; ?>
</table>
