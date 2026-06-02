<?php

class Sanitizador
{
    public static function texto(string $valor): string
    {
        $valor = trim($valor);
        $valor = strip_tags($valor);
        $valor = preg_replace('/\s+/', ' ', $valor);
        return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
    }

    public static function correo(string $correo): string
    {
        $correo = trim($correo);
        $correo = strtolower($correo);
        return filter_var($correo, FILTER_SANITIZE_EMAIL);
    }

    public static function sexo(string $sexo): string
    {
        $sexo = strtoupper(trim($sexo));

        if ($sexo === "M" || $sexo === "F") {
            return $sexo;
        }

        return "";
    }

    public static function codigo2FA(string $codigo): string
    {
        return preg_replace('/\D/', '', $codigo);
    }

    public static function secreto2FA(string $secret): string
    {
        return preg_replace('/[^A-Z2-7=]/', '', strtoupper($secret));
    }
}