<?php
session_start();
require 'csrf.php';

$token = generarTokenCSRF();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="css/global.css">
</head>
<body>

    <div class="contenedor">

        <h2>Registro de Usuario</h2>

        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="mensaje mensaje-info">
                <?php echo $_SESSION['mensaje']; ?>
            </div>
            <?php unset($_SESSION['mensaje']); ?>
        <?php endif; ?>

        <form id="formRegistro" method="POST" action="procesar_registro.php">

            <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">

            <label>Nombre:</label>
            <input type="text" name="nombre" required>

            <label>Apellido:</label>
            <input type="text" name="apellido" required>

            <label>Correo electrónico:</label>
            <input type="email" name="usuario" id="usuario" required>
            <small id="mensajeCorreo" class="ayuda"></small>

            <label>Contraseña:</label>
            <input type="password" name="password" required minlength="6">

            <label>Confirmar contraseña:</label>
            <input type="password" name="confirmar_password" required minlength="6">

            <label>Sexo:</label>
            <select name="sexo" required>
                <option value="">Seleccione</option>
                <option value="M">Masculino</option>
                <option value="F">Femenino</option>
            </select>

            <button type="submit" id="btnRegistro">Registrarse</button>

        </form>

        <div class="enlace">
            <a href="login_form.php">Ya tengo cuenta</a>
        </div>

    </div>

    <script>
        const campoCorreo = document.getElementById("usuario");
        const mensajeCorreo = document.getElementById("mensajeCorreo");
        const btnRegistro = document.getElementById("btnRegistro");

        campoCorreo.addEventListener("blur", function () {
            const correo = campoCorreo.value.trim();

            if (correo === "") {
                return;
            }

            fetch("verificar_correo.php?usuario=" + encodeURIComponent(correo))
                .then(response => response.json())
                .then(data => {
                    if (data.existe) {
                        mensajeCorreo.textContent = "Este correo ya está registrado.";
                        mensajeCorreo.className = "ayuda ayuda-error";
                        btnRegistro.disabled = true;
                    } else {
                        mensajeCorreo.textContent = "Correo disponible.";
                        mensajeCorreo.className = "ayuda ayuda-exito";
                        btnRegistro.disabled = false;
                    }
                });
        });
    </script>

</body>
</html>