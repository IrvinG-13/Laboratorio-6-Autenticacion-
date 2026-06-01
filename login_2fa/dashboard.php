<?php
session_start();

if (!isset($_SESSION['login_validado']) || $_SESSION['login_validado'] !== true) {
    header("Location: login_form.php");
    exit;
}

$nombre = htmlspecialchars($_SESSION['nombre']);
$usuario = htmlspecialchars($_SESSION['usuario']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Principal</title>
</head>
<body>

    <h2>Bienvenido, <?php echo $nombre; ?></h2>

    <p>Has iniciado sesión correctamente.</p>
    <p>Usuario: <?php echo $usuario; ?></p>

    <a href="logout.php">Cerrar sesión</a>

</body>
</html>