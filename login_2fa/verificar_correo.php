<?php
require 'db.php';
require 'clases/Sanitizador.php';

header('Content-Type: application/json');

$correo = $_GET['usuario'] ?? "";
$correo = Sanitizador::correo($correo);

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "existe" => false,
        "valido" => false
    ]);
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM usuarios WHERE Usuario = ?");
$stmt->execute([$correo]);

echo json_encode([
    "existe" => $stmt->rowCount() > 0,
    "valido" => true
]);