# Planilla de Horas Extras y Toneladas
Proyecto MVC (PHP backend) + Frontend (HTML/JS) pensado para correr en XAMPP (Apache + PHP + MySQL).

## Estructura
- backend/src: PHP MVC (rutas, controladores, modelos, config)
- frontend/src: HTML, JS (Axios), vistas y componentes
- database/init.sql: script para crear la base de datos y tablas

## Cómo usar
1. Copia la carpeta `backend` dentro de `htdocs` de XAMPP, y `frontend` dentro de `htdocs` también, o pega todo el proyecto en `C:\xampp\htdocs\planilla_horas_toneladas`.
2. Importa `database/init.sql` en tu servidor MySQL (phpMyAdmin o mysql CLI).
3. Ajusta `backend/src/config/config.php` con tu usuario y contraseña MySQL.
4. Inicia XAMPP (Apache + MySQL) y abre `http://planilla_horas_toneladas/frontend/src/index.html`

## Notas
- El sistema usa sesiones de PHP para autenticación. En llamadas con Axios se envían `withCredentials=true`.
- Los cálculos (horas extras, recargos) se realizan en el frontend al crear registro, y el backend guarda los valores calculados.
- Este código sirve como base; revisa y adapta validaciones y seguridad (sal de producción: hashing, CSRF, validaciones, etc.).

Nombre de la base de datos: bd_planillas

para pruebas: identificacion='9999'; nombres='Test'; apellidos='User'; correo='test@example.com'; clave='test123'; 