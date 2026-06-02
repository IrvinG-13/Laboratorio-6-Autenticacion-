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
require 'clases/Sanitizador.php';
$codigo = Sanitizador::codigo2FA($_POST['codigo_2fa'] ?? "");

$stmt = $pdo->prepare("SELECT secret_2fa FROM usuarios WHERE id = ?");
$stmt->execute([$usuario_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$secret = $user['secret_2fa'];

$g = new GoogleAuthenticator();

if ($g->checkCode($secret, $codigo)) {
    $_SESSION['login_validado'] = true;
    $_SESSION['2fa_verificado'] = true;

    file_put_contents("registro.log", date("Y-m-d H:i:s") . " - 2FA activado correctamente: " . $_SESSION['usuario'] . PHP_EOL, FILE_APPEND);

    header("Location: dashboard.php");
    exit;
} else {
    file_put_contents("registro.log", date("Y-m-d H:i:s") . " - Código 2FA incorrecto al activar: " . $_SESSION['usuario'] . PHP_EOL, FILE_APPEND);

    echo "Código incorrecto. <a href='activar_2fa.php'>Intentar nuevamente</a>";
}
?>