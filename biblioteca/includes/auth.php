<?php
// includes/auth.php
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.html");
        exit();
    }
}
?>