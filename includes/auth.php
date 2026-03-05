<?php
// ── Sesión y autenticación ─────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function estaLogueado(): bool {
    return isset($_SESSION['admin_id']);
}

function requireLogin(): void {
    if (!estaLogueado()) {
        header('Location: index.php');
        exit;
    }
}

function login(string $usuario, string $pass): bool {
    require_once __DIR__ . '/db.php';
    $pdo  = getDB();
    $stmt = $pdo->prepare("SELECT id, password FROM admins WHERE usuario = ?");
    $stmt->execute([$usuario]);
    $row  = $stmt->fetch();

    if ($row && password_verify($pass, $row['password'])) {
        $_SESSION['admin_id']      = $row['id'];
        $_SESSION['admin_usuario'] = $usuario;
        return true;
    }
    return false;
}

function logout(): void {
    session_destroy();
    header('Location: index.php');
    exit;
}
