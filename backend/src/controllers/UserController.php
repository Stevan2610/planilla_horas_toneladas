<?php
class UserController {
    private $pdo;
    public function __construct($pdo){ $this->pdo = $pdo; }

    private function requireAdmin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user']) || ($_SESSION['user']['cargo'] ?? '') !== 'admin') {
            http_response_code(401);
            echo json_encode(['error' => 'No autorizado - se requiere cargo admin']);
            exit;
        }
    }

    public function listAll(){
        $this->requireAdmin();
        $stmt = $this->pdo->query(
            "SELECT id, identificacion, nombres, apellidos, correo, celular, cargo, unidad_operativa, created_at,
             (CASE WHEN clave IS NOT NULL AND clave <> '' THEN 1 ELSE 0 END) as has_password
             FROM users"
        );
        echo json_encode(['data'=>$stmt->fetchAll()]);
    }

    public function delete(){
        $this->requireAdmin();
        // aceptar JSON body o form
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $id = $data['id'] ?? null;
        if (!$id) { http_response_code(400); echo json_encode(['error'=>'Falta id']); exit; }

        // prevenir eliminación del admin principal (id = 1)
        if ((int)$id === 1) {
            http_response_code(400);
            echo json_encode(['error' => 'Operación no permitida: no se puede eliminar al usuario administrador principal.']);
            exit;
        }

        $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = ?');
        try {
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'deleted' => $stmt->rowCount()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo eliminar', 'message' => $e->getMessage()]);
        }
    }

    public function update(){
        $this->requireAdmin();
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $id = $data['id'] ?? null;
        if (!$id) { http_response_code(400); echo json_encode(['error'=>'Falta id']); exit; }

        // Campos permitidos
        $fields = ['identificacion','nombres','apellidos','correo','celular','cargo','unidad_operativa','clave'];
        $updates = [];
        $params = [];
        foreach ($fields as $f) {
            if (isset($data[$f]) && $f !== 'clave') {
                $updates[] = "$f = ?";
                $params[] = $data[$f];
            }
        }

        // tratar clave por separado (hash)
        if (!empty($data['clave'])) {
            $updates[] = 'clave = ?';
            $params[] = password_hash($data['clave'], PASSWORD_BCRYPT);
        }

        if (empty($updates)) { echo json_encode(['success'=>true,'message'=>'Nada para actualizar']); exit; }

        $params[] = $id;
        $sql = 'UPDATE users SET ' . implode(', ', $updates) . ' WHERE id = ?';
        $stmt = $this->pdo->prepare($sql);
        try {
            $stmt->execute($params);
            echo json_encode(['success'=>true,'updated'=>$stmt->rowCount()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error'=>'Error actualizando','message'=>$e->getMessage()]);
        }
    }
}
