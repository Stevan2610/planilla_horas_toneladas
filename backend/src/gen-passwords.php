<?php
// Script para generar contraseñas bcrypt
header('Content-Type: application/json; charset=utf-8');

// Contraseñas simples para desarrollo
$password_admin = 'admin123';
$password_auxiliar = 'auxiliar123';

$hash_admin = password_hash($password_admin, PASSWORD_BCRYPT);
$hash_auxiliar = password_hash($password_auxiliar, PASSWORD_BCRYPT);

echo json_encode([
    'admin' => [
        'id' => '0001',
        'nombres' => 'Admin',
        'apellidos' => 'Sistema',
        'correo' => 'admin@example.com',
        'password' => $password_admin,
        'hash' => $hash_admin
    ],
    'auxiliar' => [
        'id' => '1115943980',
        'nombres' => 'DIEGO FEERNANDO',
        'apellidos' => 'SANCHEZ LISCANO',
        'correo' => 'dfsl20@gmail.com',
        'password' => $password_auxiliar,
        'hash' => $hash_auxiliar
    ],
    'sql_update' => "
-- Actualizar contraseñas de desarrollo
UPDATE users SET clave = '{$hash_admin}' WHERE identificacion = '0001';
UPDATE users SET clave = '{$hash_auxiliar}' WHERE identificacion = '1115943980';"
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
