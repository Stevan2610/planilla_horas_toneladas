<?php
header('Content-Type: application/json; charset=utf-8');

$config = require __DIR__ . '/config/config.php';

try {
    $dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['db_user'], $config['db_pass']);
    
    // Verificar tabla users
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Contar usuarios
    $countStmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $countResult = $countStmt->fetch(PDO::FETCH_ASSOC);
    
    // Obtener usuarios sin mostrar contraseña
    $usersStmt = $pdo->query("SELECT id, identificacion, nombres, apellidos, correo, cargo, unidad_operativa FROM users LIMIT 10");
    $users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'message' => 'Base de datos verificada',
        'tabla_users' => [
            'columnas' => $columns,
            'total_usuarios' => $countResult['total'],
            'usuarios' => $users
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error en la BD',
        'message' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>
