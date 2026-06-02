<?php
session_start();

if (
    !isset($_SESSION['login_validado']) ||
    $_SESSION['login_validado'] !== true ||
    !isset($_SESSION['2fa_verificado']) ||
    $_SESSION['2fa_verificado'] !== true
) {
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
    <link rel="stylesheet" href="css/global.css">
</head>
<body>

    <div class="contenedor">

        <h2>Panel Principal</h2>

        <div class="info-usuario">
            <p><strong>Bienvenido, <?php echo $nombre; ?></strong></p>
            <p>Has iniciado sesión correctamente con autenticación 2FA.</p>
            <p><strong>Usuario:</strong> <?php echo $usuario; ?></p>
        </div>

        <a class="btn-salir" href="logout.php">Cerrar sesión</a>

    </div>

</body>
</html>