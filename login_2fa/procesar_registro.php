<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $usuario = trim($_POST['usuario']);
    $password = trim($_POST['password']);
    $sexo = trim($_POST['sexo']);

    if ($nombre === "" || $apellido === "" || $usuario === "" || $password === "" || $sexo === "") {
        $_SESSION['mensaje'] = "Todos los campos son obligatorios.";
        header("Location: registro_form.php");
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE Usuario = ?");
        $stmt->execute([$usuario]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['mensaje'] = "Ese correo ya está registrado.";
            header("Location: registro_form.php");
            exit;
        }

        $hashPassword = password_hash($password, PASSWORD_DEFAULT);

        $insertar = $pdo->prepare("INSERT INTO usuarios (Nombre, Apellido, Usuario, HashMagic, Sexo) VALUES (?, ?, ?, ?, ?)");
        $insertar->execute([$nombre, $apellido, $usuario, $hashPassword, $sexo]);

        file_put_contents("registro.log", date("Y-m-d H:i:s") . " - Usuario registrado: $usuario" . PHP_EOL, FILE_APPEND);

        $_SESSION['mensaje'] = "Usuario registrado correctamente.";
        header("Location: registro_form.php");
        exit;

    } catch (PDOException $e) {
        $_SESSION['mensaje'] = "Error al registrar usuario: " . $e->getMessage();
        header("Location: registro_form.php");
        exit;
    }

} else {
    header("Location: registro_form.php");
    exit;
}
?>