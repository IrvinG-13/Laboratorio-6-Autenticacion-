<?php
session_start();
require 'db.php';
require __DIR__ . '/../vendor/autoload.php';

use Sonata\GoogleAuthenticator\GoogleAuthenticator;

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login_form.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$correo = $_SESSION['usuario'];

$stmt = $pdo->prepare("SELECT secret_2fa FROM usuarios WHERE id = ?");
$stmt->execute([$usuario_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$g = new GoogleAuthenticator();

if (empty($user['secret_2fa'])) {
    $secret = $g->generateSecret();

    $actualizar = $pdo->prepare("UPDATE usuarios SET secret_2fa = ? WHERE id = ?");
    $actualizar->execute([$secret, $usuario_id]);
} else {
    $secret = $user['secret_2fa'];
}

$app = "Login2FA";

$label = rawurlencode($app . ":" . $correo);
$issuer = rawurlencode($app);

$otpauthUrl = "otpauth://totp/{$label}?secret={$secret}&issuer={$issuer}";

$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . rawurlencode($otpauthUrl);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Activar 2FA</title>
    <link rel="stylesheet" href="css/global.css">
</head>
<body>

    <div class="contenedor contenedor-grande">

        <h2>Activar Autenticación de Dos Factores</h2>

        <p>Escanea este código QR con Google Authenticator.</p>

        <img class="qr" src="<?php echo $qr_url; ?>" alt="QR Google Authenticator">

        <p><strong>Clave secreta manual:</strong></p>

        <div class="codigo-secreto">
            <?php echo htmlspecialchars($secret); ?>
        </div>

        <p>
            Si el QR no funciona, abre Google Authenticator y usa la opción de ingresar clave manualmente.
        </p>

        <form method="POST" action="confirmar_2fa.php">
            <label>Código 2FA:</label>
            <input type="text" name="codigo_2fa" maxlength="7" inputmode="numeric" placeholder="000 000" required>

            <button type="submit">Confirmar 2FA</button>
        </form>

        <div class="enlace">
            <a href="logout.php">Cancelar y cerrar sesión</a>
        </div>

    </div>

</body>
</html>