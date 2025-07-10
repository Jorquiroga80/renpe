<?php
require '../vendor/autoload.php';
use Firebase\JWT\JWT;

// Cargar usuarios desde JSON
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
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ingreso Provincial RENPE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container">
    <div class="row justify-content-center align-items-center vh-100">
        <div class="col-md-6">
            <div class="card shadow border-0 rounded-4">
                <div class="card-body p-5">
                    <h2 class="mb-4 text-center">Ingreso al sistema provincial</h2>
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="cuil" class="form-label">CUIL</label>
                            <input type="text" class="form-control" name="cuil" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña (CUIL nuevamente)</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Ingresar</button>
                    </form>
                </div>
                <div class="card-footer text-muted text-center small">
                    RENPE · Ministerio de Educación
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

