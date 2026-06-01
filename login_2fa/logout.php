<?php
session_start();

file_put_contents("registro.log", date("Y-m-d H:i:s") . " - Cierre de sesión: " . ($_SESSION['usuario'] ?? 'usuario desconocido') . PHP_EOL, FILE_APPEND);

session_destroy();

header("Location: login_form.php");
exit;
?>