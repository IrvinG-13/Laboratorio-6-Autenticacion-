<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login_form.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificar 2FA</title>
    <link rel="stylesheet" href="css/global.css">
</head>
<body>

    <div class="contenedor">

        <h2>Verificación de Dos Factores</h2>

        <p>Ingrese el código de Google Authenticator para completar el inicio de sesión.</p>

        <form method="POST" action="validar_2fa.php">
            <label>Código 2FA:</label>
            <input type="text" name="codigo_2fa" maxlength="7" inputmode="numeric" placeholder="000 000" required>

            <button type="submit">Verificar</button>
        </form>

        <div class="enlace">
            <a href="logout.php">Cancelar y cerrar sesión</a>
        </div>

    </div>

</body>
</html>