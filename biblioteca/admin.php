<?php
require_once 'includes/auth.php';
requireLogin();

/* ── Cerrar sesión ───────────────────────────────────────────────────────── */
if (isset($_GET['logout'])) {
    logout(); // redirige a index.php
}

/* ── Eliminar usuario ───────────────────────────────────────────────────── */
if (isset($_POST['eliminar_id'])) {
    $pdo  = getDB();
    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->execute([(int)$_POST['eliminar_id']]);
    header('Location: admin.php');
    exit;
}

/* ── Editar usuario ─────────────────────────────────────────────────────── */
if (isset($_POST['editar_id'])) {
    $pdo  = getDB();
    $stmt = $pdo->prepare(
        "UPDATE usuarios SET nombre=?, area=?, correo=?, telefono=? WHERE id=?"
    );
    $stmt->execute([
        trim($_POST['nombre']),
        trim($_POST['area']),
        trim($_POST['correo']),
        trim($_POST['telefono']),
        (int)$_POST['editar_id'],
    ]);
    header('Location: admin.php');
    exit;
}

/* ── Parámetros del filtro de mes ───────────────────────────────────────── */
$mes  = (int)($_GET['mes']  ?? date('n'));
$anio = (int)($_GET['anio'] ?? date('Y'));

// Navegar mes anterior / siguiente
if (isset($_GET['nav'])) {
    $dt = new DateTime("$anio-$mes-01");
    $dt->modify($_GET['nav'] === 'prev' ? '-1 month' : '+1 month');
    $mes  = (int)$dt->format('n');
    $anio = (int)$dt->format('Y');
    header("Location: admin.php?mes=$mes&anio=$anio");
    exit;
}

/* ── Consulta de usuarios del mes ───────────────────────────────────────── */
$pdo    = getDB();
$filtro = trim($_GET['q'] ?? '');

$sql = "SELECT * FROM usuarios
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

/* ── Etiqueta del mes ────────────────────────────────────────────────────── */
$meses = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio',
          'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
$etiquetaMes = $meses[$mes] . ' ' . $anio;

/* ── Usuario en edición (si viene ?editar=ID) ────────────────────────────── */
$usuarioEdit = null;
if (isset($_GET['editar'])) {
    $stmtE       = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmtE->execute([(int)$_GET['editar']]);
    $usuarioEdit = $stmtE->fetch();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Admin – Biblioteca ITLA</title>
  <link rel="stylesheet" href="css/admin.css">
</head>
<body>
<div class="layout">

  <!-- Sidebar -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-top">
      <h2>ITLA Biblioteca</h2>
      <hr>
    </div>
    <div class="sidebar-content">
      <div class="admin-box">
        👤 <?= htmlspecialchars($_SESSION['admin_usuario']) ?>
      </div>
      <button class="menu-btn activo">👥 Usuarios</button>
      <button class="menu-btn">📚 Libros</button>
      <a href="admin.php?logout=1" class="menu-btn" style="display:block;text-decoration:none;margin-top:20px;">
        🚪 Cerrar sesión
      </a>
    </div>
  </aside>

  <!-- Main -->
  <div class="main">

    <!-- Topbar -->
    <header class="topbar">
      <button class="menu-toggle" onclick="toggleSidebar()">☰</button>
      <div class="topbar-right">
        <form method="GET" action="admin.php" style="display:flex;gap:8px;align-items:center;">
          <input type="hidden" name="mes"  value="<?= $mes ?>">
          <input type="hidden" name="anio" value="<?= $anio ?>">
          <input type="text" name="q" class="buscador"
                 placeholder="Buscar por nombre, matrícula o correo..."
                 value="<?= htmlspecialchars($filtro) ?>">
          <button type="submit" class="filtro-btn">🔍</button>
        </form>
      </div>
    </header>

    <div class="linea-superior"></div>
    <h1 class="titulo">Listado de Usuarios</h1>

    <!-- Modal de edición -->
    <?php if ($usuarioEdit): ?>
    <div class="modal-overlay">
      <div class="modal-box">
        <h3>✏️ Editar Usuario</h3>
        <form method="POST" action="admin.php?mes=<?= $mes ?>&anio=<?= $anio ?>">
          <input type="hidden" name="editar_id" value="<?= $usuarioEdit['id'] ?>">
          <label>Nombre</label>
          <input type="text" name="nombre" value="<?= htmlspecialchars($usuarioEdit['nombre']) ?>" required>
          <label>Área / Carrera</label>
          <input type="text" name="area" value="<?= htmlspecialchars($usuarioEdit['area']) ?>" required>
          <label>Correo</label>
          <input type="email" name="correo" value="<?= htmlspecialchars($usuarioEdit['correo']) ?>" required>
          <label>Teléfono</label>
          <input type="tel" name="telefono" value="<?= htmlspecialchars($usuarioEdit['telefono']) ?>" required>
          <div class="modal-btns">
            <button type="submit" class="filtro-btn">💾 Guardar</button>
            <a href="admin.php?mes=<?= $mes ?>&anio=<?= $anio ?>" class="filtro-btn" style="text-decoration:none;">✖ Cancelar</a>
          </div>
        </form>
      </div>
    </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-header">
        Usuarios registrados en biblioteca
      </div>

      <!-- Filtros de mes -->
      <div class="filtro">
        <a href="admin.php?nav=prev&mes=<?= $mes ?>&anio=<?= $anio ?>&q=<?= urlencode($filtro) ?>"
           class="filtro-btn" style="text-decoration:none;">◀ Mes</a>

        <span style="margin:0 10px;font-weight:bold;font-size:1.1em;">
          <?= $etiquetaMes ?>
        </span>

        <a href="admin.php?nav=next&mes=<?= $mes ?>&anio=<?= $anio ?>&q=<?= urlencode($filtro) ?>"
           class="filtro-btn" style="text-decoration:none;">Mes ▶</a>

        <a href="exportar.php?mes=<?= $mes ?>&anio=<?= $anio ?>&q=<?= urlencode($filtro) ?>"
           class="exportar-btn" style="text-decoration:none;">
          📊 Exportar Excel
        </a>

        <span style="margin-left:15px;font-size:0.95em;color:#555;">
          Usuarios en el mes: <strong><?= count($usuarios) ?></strong>
        </span>
      </div>

      <!-- Tabla -->
      <table id="tablaUsuarios">
        <thead>
          <tr>
            <th>Foto</th>
            <th>Nombre</th>
            <th>Matrícula</th>
            <th>Tipo</th>
            <th>Área</th>
            <th>Correo</th>
            <th>Teléfono</th>
            <th>Fecha</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($usuarios)): ?>
            <tr>
              <td colspan="9" style="text-align:center;color:#888;padding:20px;">
                No hay usuarios registrados en <?= $etiquetaMes ?>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($usuarios as $u): ?>
            <tr>
              <td>
                <?php if ($u['foto']): ?>
                  <img src="img/fotos/<?= htmlspecialchars($u['foto']) ?>"
                       alt="foto" style="width:40px;height:40px;border-radius:50%;object-fit:cover;">
                <?php else: ?>
                  —
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars($u['nombre']) ?></td>
              <td><?= htmlspecialchars($u['matricula']) ?></td>
              <td><?= htmlspecialchars($u['tipo']) ?></td>
              <td><?= htmlspecialchars($u['area']) ?></td>
              <td><?= htmlspecialchars($u['correo']) ?></td>
              <td><?= htmlspecialchars($u['telefono']) ?></td>
              <td><?= htmlspecialchars($u['fecha']) ?></td>
              <td>
                <!-- Editar -->
                <a href="admin.php?editar=<?= $u['id'] ?>&mes=<?= $mes ?>&anio=<?= $anio ?>"
                   title="Editar">✏️</a>

                <!-- Eliminar -->
                <form method="POST" action="admin.php?mes=<?= $mes ?>&anio=<?= $anio ?>"
                      style="display:inline;"
                      onsubmit="return confirm('¿Eliminar a <?= htmlspecialchars(addslashes($u['nombre'])) ?>?')">
                  <input type="hidden" name="eliminar_id" value="<?= $u['id'] ?>">
                  <button type="submit" style="background:none;border:none;cursor:pointer;font-size:1em;"
                          title="Eliminar">🗑</button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div><!-- /.card -->

  </div><!-- /.main -->
</div><!-- /.layout -->

<script>
function toggleSidebar() {
  document.getElementById("sidebar").classList.toggle("oculto");
}
</script>
</body>
</html>
