<?php

class RegistroUsuario
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function correoExiste(string $correo): bool
    {
        $stmt = $this->pdo->prepare("SELECT id FROM usuarios WHERE Usuario = ?");
        $stmt->execute([$correo]);

        return $stmt->rowCount() > 0;
    }

    public function guardar(array $datos): bool
    {
        $hashPassword = password_hash($datos['password'], PASSWORD_DEFAULT);

        $stmt = $this->pdo->prepare(
            "INSERT INTO usuarios (Nombre, Apellido, Usuario, HashMagic, Sexo) 
             VALUES (?, ?, ?, ?, ?)"
        );

        return $stmt->execute([
            $datos['nombre'],
            $datos['apellido'],
            $datos['usuario'],
            $hashPassword,
            $datos['sexo']
        ]);
    }
}