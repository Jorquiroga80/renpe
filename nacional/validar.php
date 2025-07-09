<?php
require '../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$clave_secreta = 'clave_super_segura';
$token_usados_file = __DIR__ . '/tokens_usados.json';

if (!isset($_GET['token'])) {
    die("Token no proporcionado");
}

$token = $_GET['token'];

try {
    $datos = JWT::decode($token, new Key($clave_secreta, 'HS256'));

    $jti = $datos->jti ?? null;
    $tokens_usados = file_exists($token_usados_file) ? json_decode(file_get_contents($token_usados_file), true) : [];

    if (!$jti || in_array($jti, $tokens_usados)) {
        die("Token ya fue utilizado o inválido.");
    }

    $tokens_usados[] = $jti;
    file_put_contents($token_usados_file, json_encode($tokens_usados));

    echo "<h2>Bienvenido, {$datos->nombre} {$datos->apellido}</h2>";
    echo "<p>Email: {$datos->email}</p>";
    echo "<p>ID de usuario: {$datos->user_id}</p>";
    echo "<p>CUE: " . implode(', ', $datos->CUE) . "</p>";

} catch (Exception $e) {
    echo "Token inválido: " . $e->getMessage();
}
?>
