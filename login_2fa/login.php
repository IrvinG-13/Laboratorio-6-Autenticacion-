<?php
session_start();
require 'db.php';
require 'csrf.php';
require 'clases/Sanitizador.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST['csrf_token'] ?? "";

    if (!verificarTokenCSRF($token)) {
        $_SESSION['mensaje'] = "Solicitud inválida. Token CSRF incorrecto.";
        header("Location: login_form.php");
        exit;
    }

    $usuario = Sanitizador::correo($_POST['usuario'] ?? "");
$password = trim($_POST['password'] ?? "");
    $ip = $_SERVER['REMOTE_ADDR'];

    if ($usuario === "" || $password === "") {
        $_SESSION['mensaje'] = "Debe ingresar correo y contraseña.";
        header("Location: login_form.php");
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE Usuario = ?");
        $stmt->execute([$usuario]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['HashMagic'])) {

            file_put_contents("registro.log", date("Y-m-d H:i:s") . " - Usuario y contraseña correctos: $usuario" . PHP_EOL, FILE_APPEND);

            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['usuario'] = $user['Usuario'];
            $_SESSION['nombre'] = $user['Nombre'];

            // Todavía NO damos acceso completo.
            // Primero debe pasar por 2FA.
            $_SESSION['login_validado'] = false;
            $_SESSION['2fa_verificado'] = false;

            if (empty($user['secret_2fa'])) {
                header("Location: activar_2fa.php");
                exit;
            } else {
                header("Location: verificar_2fa.php");
                exit;
            }

        } else {

            $audit = $pdo->prepare("INSERT INTO intentos_login (Usuario, ipRemoto, estado, deteccion_anomala) VALUES (?, ?, ?, ?)");
            $audit->execute([$usuario, $ip, 'fail', 1]);

            file_put_contents("registro.log", date("Y-m-d H:i:s") . " - Login fallido: $usuario" . PHP_EOL, FILE_APPEND);

            $_SESSION['mensaje'] = "Usuario o contraseña incorrectos.";
            header("Location: login_form.php");
            exit;
        }

    } catch (PDOException $e) {
        $_SESSION['mensaje'] = "Error en el login: " . $e->getMessage();
        header("Location: login_form.php");
        exit;
    }

} else {
    header("Location: login_form.php");
    exit;
}
?>