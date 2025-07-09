<?php
require '../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Configuración de PostgreSQL (Railway)
$host = 'gondola.proxy.rlwy.net';
$port = '19875';
$dbname = 'railway';
$user = 'postgres';
$password = 'njmkBQvvWAixyTXYRjccqQoHKGlEoeaE';

$clave_secreta = 'clave_super_segura';

if (!isset($_GET['token'])) {
    die("Token no proporcionado");
}

$token = $_GET['token'];

try {
    $datos = JWT::decode($token, new Key($clave_secreta, 'HS256'));
    $jti = $datos->jti ?? null;

    if (!$jti) {
        die("Token inválido: no tiene jti");
    }

    // Conectar a PostgreSQL
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verificar si el jti ya fue usado
    $stmt = $conn->prepare("SELECT COUNT(*) FROM tokens_usados WHERE jti = :jti");
    $stmt->execute([':jti' => $jti]);
    $usado = $stmt->fetchColumn();

    if ($usado > 0) {
        die("Este token ya fue utilizado.");
    }

    // Registrar el jti
    $stmt = $conn->prepare("INSERT INTO tokens_usados (jti) VALUES (:jti)");
    $stmt->execute([':jti' => $jti]);

    // Mostrar datos
    echo "<h2>Bienvenido, {$datos->nombre} {$datos->apellido}</h2>";
    echo "<p>Email: {$datos->email}</p>";
    echo "<p>ID de usuario: {$datos->user_id}</p>";
    echo "<p>CUE: " . implode(', ', $datos->CUE) . "</p>";

} catch (Exception $e) {
    echo "Token inválido: " . $e->getMessage();
}
?>
