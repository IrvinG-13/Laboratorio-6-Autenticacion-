<?php
session_start();
require 'csrf.php';

$token = generarTokenCSRF();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="css/global.css">
</head>
<body>

    <div class="contenedor">

        <h2>Iniciar Sesión</h2>

        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="mensaje mensaje-error">
                <?php echo $_SESSION['mensaje']; ?>
            </div>
            <?php unset($_SESSION['mensaje']); ?>
        <?php endif; ?>

        <form method="POST" action="login.php">

            <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">

            <label>Correo electrónico:</label>
            <input type="email" name="usuario" required>

            <label>Contraseña:</label>
            <input type="password" name="password" required>

            <button type="submit">Iniciar sesión</button>

        </form>

        <div class="enlace">
            <a href="registro_form.php">Crear cuenta</a>
        </div>

        <div class="enlace">
            <a href="hash_test.php">Probar hash de contraseña</a>
        </div>

    </div>

</body>
</html>