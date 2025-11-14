<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Test: Crear una sesión simulada
if (isset($_GET['test'])) {
    $_SESSION['user'] = [
        'id' => 1,
        'identificacion' => '123456789',
        'nombres' => 'Juan',
        'apellidos' => 'Perez',
        'cargo' => 'auxiliar',
        'unidad_operativa' => 'Cali'
    ];
    echo json_encode([
        'success' => true,
        'message' => 'Sesión de prueba creada',
        'user' => $_SESSION['user'],
        'session_id' => session_id(),
        'cookies' => $_COOKIE
    ]);
    exit;
}

// Verificar sesión actual
if (empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode([
        'error' => 'No hay sesión',
        'session_id' => session_id(),
        'session_data' => $_SESSION,
        'cookies' => $_COOKIE
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'user' => $_SESSION['user'],
    'session_id' => session_id(),
    'cookies' => $_COOKIE
]);
?>
