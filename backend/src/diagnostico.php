<?php
// Script de diagnóstico

echo "<h2>Diagnóstico del Sistema</h2>";

// 1. Verificar conexión a BD
echo "<h3>1. Conexión a Base de Datos:</h3>";
$config = require __DIR__ . '/config/config.php';
try {
    $dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['db_user'], $config['db_pass']);
    echo "<p style='color:green;'>✅ Conexión a MySQL exitosa</p>";
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Error de conexión: " . $e->getMessage() . "</p>";
}

// 2. Verificar sesiones
echo "<h3>2. Sesiones PHP:</h3>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
echo "<p style='color:green;'>✅ Sesiones habilitadas</p>";

// 3. Verificar que la ruta es accesible
echo "<h3>3. Rutas de API:</h3>";
echo "<ul>";
echo "<li><strong>Base URL:</strong> " . $config['base_url'] . "</li>";
echo "<li><strong>Auth Login:</strong> " . $config['base_url'] . "/index.php?path=auth/login</li>";
echo "<li><strong>Auth Me:</strong> " . $config['base_url'] . "/index.php?path=auth/me</li>";
echo "</ul>";

// 4. Verificar directorio de sesiones
echo "<h3>4. Directorio de Sesiones:</h3>";
echo "<p><strong>Session Save Path:</strong> " . ini_get('session.save_path') . "</p>";
echo "<p><strong>Session Save Handler:</strong> " . ini_get('session.save_handler') . "</p>";

echo "<h3>✅ Diagnóstico completado</h3>";
?>
