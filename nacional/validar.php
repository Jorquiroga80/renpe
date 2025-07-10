<?php
require '../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Configuración de PostgreSQL (Railway)
$host = 'switchyard.proxy.rlwy.net';
$port = '29047';
$dbname = 'railway';
$user = 'postgres';
$password = 'hLueOYLZyBLtbPkHWmXLScNRgzsUUelw';

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

    // ✅ Registrar acceso en CSV
    $log_file = __DIR__ . '/log_ingresos.csv';
    $fecha = date("Y-m-d H:i:s");
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'IP_desconocida';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido';
    $fila = [
        $fecha,
        $datos->user_id ?? '',
        $datos->nombre ?? '',
        $datos->apellido ?? '',
        implode(" - ", $datos->CUE ?? []),
        $ip,
        $user_agent
    ];
    file_put_contents($log_file, implode(";", $fila) . "\n", FILE_APPEND);

    // Mostrar datos
    echo "<h2>Bienvenido, {$datos->nombre} {$datos->apellido}</h2>";
    echo "<p>Email: {$datos->email}</p>";
    echo "<p>ID de usuario: {$datos->user_id}</p>";
    echo "<p>CUE: " . implode(', ', $datos->CUE) . "</p>";

} catch (Exception $e) {
    echo "Token inválido: " . $e->getMessage();
}
?>
