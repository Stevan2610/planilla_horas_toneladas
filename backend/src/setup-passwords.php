<?php
header('Content-Type: application/json; charset=utf-8');

$config = require __DIR__ . '/config/config.php';

try {
    $dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['db_user'], $config['db_pass']);
    
    // Generar hashes seguros
    $hash_admin = password_hash('admin123', PASSWORD_BCRYPT);
    $hash_auxiliar = password_hash('auxiliar123', PASSWORD_BCRYPT);
    
    // Actualizar admin
    $stmt1 = $pdo->prepare("UPDATE users SET clave = ? WHERE identificacion = ?");
    $result1 = $stmt1->execute([$hash_admin, '0001']);
    
    // Actualizar auxiliar
    $stmt2 = $pdo->prepare("UPDATE users SET clave = ? WHERE identificacion = ?");
    $result2 = $stmt2->execute([$hash_auxiliar, '1115943980']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Contraseñas actualizadas correctamente',
        'usuarios_actualizados' => [
            [
                'id' => '0001',
                'nombres' => 'Admin',
                'password' => 'admin123',
                'actualizado' => $result1
            ],
            [
                'id' => '1115943980',
                'nombres' => 'DIEGO FEERNANDO SANCHEZ',
                'password' => 'auxiliar123',
                'actualizado' => $result2
            ]
        ],
        'nota' => 'Ahora puedes hacer login con estas credenciales'
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error al actualizar contraseñas',
        'message' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>
