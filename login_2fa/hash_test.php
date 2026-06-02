<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require 'csrf.php';

$token = generarTokenCSRF();
$hashGenerado = "";
$resultado = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $tokenPost = $_POST['csrf_token'] ?? "";

    if (!verificarTokenCSRF($tokenPost)) {
        $resultado = "Token CSRF inválido.";
    } else {
        $accion = $_POST['accion'] ?? "";
        $password = $_POST['password'] ?? "";
        $hash = $_POST['hash'] ?? "";

        if ($accion === "generar") {
            if ($password === "") {
                $resultado = "Debe ingresar una contraseña.";
            } else {
                $hashGenerado = password_hash($password, PASSWORD_DEFAULT);
            }
        }

        if ($accion === "validar") {
            if ($password === "" || $hash === "") {
                $resultado = "Debe ingresar la contraseña y el hash.";
            } else {
                if (password_verify($password, $hash)) {
                    $resultado = "El hash corresponde a la contraseña ingresada.";
                } else {
                    $resultado = "El hash NO corresponde a la contraseña ingresada.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Generar y Validar Hash</title>
    <link rel="stylesheet" href="css/global.css">
</head>
<body>

    <div class="contenedor contenedor-grande">

        <h2>Generar y Validar Hash</h2>

        <?php if ($resultado !== ""): ?>
            <div class="mensaje mensaje-info">
                <?php echo htmlspecialchars($resultado); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="hash_test.php">
            <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
            <input type="hidden" name="accion" value="generar">

            <label>Contraseña:</label>
            <input type="text" name="password" required>

            <button type="submit">Generar hash</button>
        </form>

        <?php if ($hashGenerado !== ""): ?>
            <p><strong>Hash generado:</strong></p>
            <div class="codigo-secreto">
                <?php echo htmlspecialchars($hashGenerado); ?>
            </div>
        <?php endif; ?>

        <hr>

        <form method="POST" action="hash_test.php">
            <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
            <input type="hidden" name="accion" value="validar">

            <label>Contraseña:</label>
            <input type="text" name="password" required>

            <label>Hash:</label>
            <input type="text" name="hash" required>

            <button type="submit">Validar hash</button>
        </form>

        <div class="enlace">
            <a href="login_form.php">Volver al login</a>
        </div>

    </div>

</body>
</html>