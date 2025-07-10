<?php
require '../vendor/autoload.php';
use Firebase\JWT\JWT;

// ✅ Cargar usuarios desde archivo JSON
$usuarios = json_decode(file_get_contents(__DIR__ . '/usuarios.json'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cuil_usuario = $_POST['cuil'] ?? '';
    $password = $_POST['password'] ?? '';

    if (isset($usuarios[$cuil_usuario]) && $password === $cuil_usuario) {
        $datos = $usuarios[$cuil_usuario];
        $jti = bin2hex(random_bytes(8));

        $payload = [
            'user_id' => $cuil_usuario,
            'nombre' => $datos['nombre'],
            'apellido' => $datos['apellido'],
            'email' => $datos['email'],
            'CUE' => $datos['cue'],
            'rol' => $datos['rol'],
            'jti' => $jti,
            'exp' => time() + 300
        ];

        $clave_secreta = 'clave_super_segura';
        $token = JWT::encode($payload, $clave_secreta, 'HS256');
        $url_redireccion = "https://renpe-production.up.railway.app/nacional/validar.php?token=$token";
        header("Location: $url_redireccion");
        exit;
    } else {
        $error = "CUIL o contraseña incorrectos.";
    }
}
?>
