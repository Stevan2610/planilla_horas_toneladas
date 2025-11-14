<?php
// controllers/AuthController.php
class AuthController {
    private $pdo;
    public function __construct($pdo){ $this->pdo = $pdo; }

    public function register(){
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) { http_response_code(400); echo json_encode(['error'=>'No data']); exit; }

        // simple validation
        $required = ['identificacion','nombres','apellidos','correo','clave','cargo','unidad_operativa'];
        foreach ($required as $r) if (empty($data[$r])) { http_response_code(400); echo json_encode(['error'=>"Falta $r"]); exit; }

        // hash password
        $hash = password_hash($data['clave'], PASSWORD_BCRYPT);

        $stmt = $this->pdo->prepare('INSERT INTO users (identificacion,nombres,apellidos,correo,clave,celular,cargo,unidad_operativa) VALUES (?,?,?,?,?,?,?,?)');
        try {
            $stmt->execute([
                $data['identificacion'],
                $data['nombres'],
                $data['apellidos'],
                $data['correo'],
                $hash,
                $data['celular'] ?? '',
                $data['cargo'],
                $data['unidad_operativa']
            ]);
            echo json_encode(['success'=>true]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error'=>'Registro falló','message'=>$e->getMessage()]);
        }
    }

    public function login(){
    if (session_status() === PHP_SESSION_NONE) session_start();

    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) { http_response_code(400); echo json_encode(['error'=>'No data']); exit; }

    $stmt = $this->pdo->prepare('SELECT * FROM users WHERE identificacion = ? LIMIT 1');
    $stmt->execute([$data['identificacion']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($data['clave'], $user['clave'])){
        http_response_code(401);
        echo json_encode(['error'=>'Credenciales inválidas']);
        exit;
    }

    // Regenerar id de sesión para seguridad y evitar colisiones
    session_regenerate_id(true);

    // guardar sesión
    $_SESSION['user'] = [
        'id' => $user['id'],
        'identificacion' => $user['identificacion'],
        'nombres' => $user['nombres'],
        'apellidos' => $user['apellidos'],
        'cargo' => $user['cargo'],
        'unidad_operativa' => $user['unidad_operativa']
    ];

    echo json_encode(['success'=>true, 'user' => $_SESSION['user']]);
}

    public function logout(){
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
        );
    }
        session_destroy();
        echo json_encode(['success'=>true]);
    }

    public function me() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user'])) {
            http_response_code(401);
            echo json_encode(['error' => 'No autorizado']);
            exit;
        }
        echo json_encode(['user' => $_SESSION['user']]);
    }

}
