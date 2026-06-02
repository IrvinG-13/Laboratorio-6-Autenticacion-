<?php
session_start();

require 'db.php';
require 'csrf.php';
require 'clases/Sanitizador.php';
require 'clases/RegistroUsuario.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: registro_form.php");
    exit;
}

$token = $_POST['csrf_token'] ?? "";

if (!verificarTokenCSRF($token)) {
    $_SESSION['mensaje'] = "Solicitud inválida. Token CSRF incorrecto.";
    header("Location: registro_form.php");
    exit;
}

$nombre = Sanitizador::texto($_POST['nombre'] ?? "");
$apellido = Sanitizador::texto($_POST['apellido'] ?? "");
$usuario = Sanitizador::correo($_POST['usuario'] ?? "");
$password = $_POST['password'] ?? "";
$confirmarPassword = $_POST['confirmar_password'] ?? "";
$sexo = Sanitizador::sexo($_POST['sexo'] ?? "");

if ($nombre === "" || $apellido === "" || $usuario === "" || $password === "" || $confirmarPassword === "" || $sexo === "") {
    $_SESSION['mensaje'] = "Todos los campos son obligatorios.";
    header("Location: registro_form.php");
    exit;
}

if (!filter_var($usuario, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['mensaje'] = "El correo electrónico no tiene un formato válido.";
    header("Location: registro_form.php");
    exit;
}

if (strlen($password) < 6) {
    $_SESSION['mensaje'] = "La contraseña debe tener mínimo 6 caracteres.";
    header("Location: registro_form.php");
    exit;
}

if ($password !== $confirmarPassword) {
    $_SESSION['mensaje'] = "Las contraseñas no coinciden.";
    header("Location: registro_form.php");
    exit;
}

try {
    $registro = new RegistroUsuario($pdo);

    if ($registro->correoExiste($usuario)) {
        $_SESSION['mensaje'] = "Ese correo ya está registrado.";
        header("Location: registro_form.php");
        exit;
    }

    $datos = [
        "nombre" => $nombre,
        "apellido" => $apellido,
        "usuario" => $usuario,
        "password" => $password,
        "sexo" => $sexo
    ];

    $registro->guardar($datos);

    file_put_contents(
        "registro.log",
        date("Y-m-d H:i:s") . " - Usuario registrado: $usuario" . PHP_EOL,
        FILE_APPEND
    );

    $_SESSION['mensaje'] = "Usuario registrado correctamente.";
    header("Location: login_form.php");
    exit;

} catch (PDOException $e) {
    $_SESSION['mensaje'] = "Error al registrar usuario: " . $e->getMessage();
    header("Location: registro_form.php");
    exit;
}