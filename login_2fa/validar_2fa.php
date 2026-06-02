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
$usuario = $_SESSION['usuario'];
require 'clases/Sanitizador.php';
$codigo = Sanitizador::codigo2FA($_POST['codigo_2fa'] ?? "");
$ip = $_SERVER['REMOTE_ADDR'];

$stmt = $pdo->prepare("SELECT secret_2fa FROM usuarios WHERE id = ?");
$stmt->execute([$usuario_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$secret = $user['secret_2fa'];

$g = new GoogleAuthenticator();

if ($g->checkCode($secret, $codigo)) {

    $_SESSION['login_validado'] = true;
    $_SESSION['2fa_verificado'] = true;

    $audit = $pdo->prepare("INSERT INTO intentos_login (Usuario, ipRemoto, estado, deteccion_anomala) VALUES (?, ?, ?, ?)");
    $audit->execute([$usuario, $ip, 'success', 0]);

    file_put_contents("registro.log", date("Y-m-d H:i:s") . " - Login completo con 2FA exitoso: $usuario" . PHP_EOL, FILE_APPEND);

    header("Location: dashboard.php");
    exit;

} else {

    $audit = $pdo->prepare("INSERT INTO intentos_login (Usuario, ipRemoto, estado, deteccion_anomala) VALUES (?, ?, ?, ?)");
    $audit->execute([$usuario, $ip, 'fail', 1]);

    file_put_contents("registro.log", date("Y-m-d H:i:s") . " - Código 2FA incorrecto: $usuario" . PHP_EOL, FILE_APPEND);

    echo "Código 2FA incorrecto. <a href='verificar_2fa.php'>Intentar nuevamente</a>";
}
?>