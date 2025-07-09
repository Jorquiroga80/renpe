<?php
require '../vendor/autoload.php';
use Firebase\JWT\JWT;

$usuarios = [
    '20343124806' => [
        'nombre' => 'Juan',
        'apellido' => 'Pérez',
        'email' => 'juan.perez@provincia.gob.ar',
        'cuil' => '20343124806',
        'cue' => ['1800123', '1800456']
    ]
];

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
            'login' => $cuil_usuario,
            'CUE' => $datos['cue'],
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
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login Provincial RENPE</title>
</head>
<body>
    <h2>Ingreso al sistema provincial - RENPE</h2>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="POST" action="">
        <label for="cuil">CUIL:</label><br>
        <input type="text" name="cuil" required><br><br>

        <label for="password">Contraseña (CUIL nuevamente):</label><br>
        <input type="password" name="password" required><br><br>

        <input type="submit" value="Ingresar">
    </form>
</body>
</html>
