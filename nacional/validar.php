<?php
require '../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$clave_secreta = 'clave_super_segura';

if (!isset($_GET['token'])) {
    die("Token no proporcionado");
}

$token = $_GET['token'];

try {
    $datos = JWT::decode($token, new Key($clave_secreta, 'HS256'));

    echo "<h2>Bienvenido, {$datos->nombre} {$datos->apellido}</h2>";
    echo "<p>Email: {$datos->email}</p>";
    echo "<p>ID de usuario: {$datos->user_id}</p>";

} catch (Exception $e) {
    echo "Token inválido: " . $e->getMessage();
}
?>
