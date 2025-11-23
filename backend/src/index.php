<?php
$frontend_origin = 'http://localhost'; // <-- igual a la URL donde abres el frontend
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: $frontend_origin");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true'); // <- importante

// 💡 Antes de iniciar sesión
if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',           // <-- permite a frontend y backend compartir cookie
    'domain' => '',          // <-- vacío para localhost
    'secure' => false,       // <-- false en HTTP (sin SSL)
    'httponly' => true,
    'samesite' => 'Lax'      // <-- Lax funciona mejor en desarrollo local HTTP
]);

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require __DIR__ . '/config/db.php';

//$path = isset($_GET['path']) ? rtrim($_GET['path'], '/') : '';
$path = isset($_GET['path']) ? ltrim(rtrim($_GET['path'], '/'), '/') : '';

function json($data){ echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }

switch ($path) {
    case 'auth/register':
        require __DIR__ . '/controllers/AuthController.php';
        (new AuthController($pdo))->register();
        break;
    case 'auth/login':
    require __DIR__ . '/controllers/AuthController.php';
    (new AuthController($pdo))->login();
    break;
    case 'auth/logout':
        require __DIR__ . '/controllers/AuthController.php';
        (new AuthController($pdo))->logout();
        break;
    case 'registros/save':
        require __DIR__ . '/controllers/RegistroController.php';
        (new RegistroController($pdo))->save();
        break;
    case 'registros/user':
        require __DIR__ . '/controllers/RegistroController.php';
        (new RegistroController($pdo))->getByUser();
        break;
    case 'registros/all':
        require __DIR__ . '/controllers/RegistroController.php';
        (new RegistroController($pdo))->getAll();
        break;
    case 'registros/update':
        require __DIR__ . '/controllers/RegistroController.php';
        (new RegistroController($pdo))->update();
        break;
    case 'registros/summary':
        require __DIR__ . '/controllers/RegistroController.php';
        (new RegistroController($pdo))->getSummaryByUser();
        break;
    case 'adicionales/all':
        require __DIR__ . '/controllers/AdicionalController.php';
        (new AdicionalController($pdo))->getAll();
        break;
    case 'adicionales/save':
        require __DIR__ . '/controllers/AdicionalController.php';
        (new AdicionalController($pdo))->save();
        break;
    case 'users/list':
        require __DIR__ . '/controllers/UserController.php';
        (new UserController($pdo))->listAll();
        break;
    case 'users/delete':
        require __DIR__ . '/controllers/UserController.php';
        (new UserController($pdo))->delete();
        break;
    case 'users/update':
        require __DIR__ . '/controllers/UserController.php';
        (new UserController($pdo))->update();
        break;
    case 'auth/me':
        require_once 'controllers/AuthController.php';
        $controller = new AuthController($pdo);
        $controller->me();
        break;
    default:
        http_response_code(404);
        json(['error' => 'Endpoint no encontrado', 'path' => $path]);
}
