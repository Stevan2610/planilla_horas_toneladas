<?php
// controllers/RegistroController.php
class RegistroController {
    private $pdo;
    public function __construct($pdo){ $this->pdo = $pdo; }

    public function save(){
        // require session user
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['user'])) { http_response_code(401); echo json_encode(['error'=>'No autorizado']); exit; }
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) { http_response_code(400); echo json_encode(['error'=>'No data']); exit; }

        // insert registro (values already calculated by frontend)
        $stmt = $this->pdo->prepare('INSERT INTO registros (user_id,fecha,hora_entrada,hora_salida,placa,numero_folio,toneladas,ton_coteadas,firma,bono_transporte,bono_alimentacion,recargo_nocturno,he_diurnas,he_nocturnas,he_dom_fest,he_diurnas_dom,he_nocturnas_dom) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
        try {
            $stmt->execute([
                $_SESSION['user']['id'],
                $data['fecha'],
                $data['hora_entrada'],
                $data['hora_salida'],
                $data['placa'],
                $data['numero_folio'],
                $data['toneladas'] ?: 0,
                $data['ton_coteadas'] ?: 0,
                $data['firma'] ?? '',
                $data['bono_transporte'] ?: 0,
                $data['bono_alimentacion'] ?: 0,
                $data['recargo_nocturno'] ?: 0,
                $data['he_diurnas'] ?: 0,
                $data['he_nocturnas'] ?: 0,
                $data['he_dom_fest'] ?: 0,
                $data['he_diurnas_dom'] ?: 0,
                $data['he_nocturnas_dom'] ?: 0
            ]);
            echo json_encode(['success'=>true]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error'=>'No se pudo guardar', 'message'=>$e->getMessage()]);
        }
    }

    public function getByUser(){
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['user'])) { http_response_code(401); echo json_encode(['error'=>'No autorizado']); exit; }
        
        // Si viene X-User-Id en header (admin consultando otro usuario)
        $userId = $_GET['user_id'] ?? $_SERVER['HTTP_X_USER_ID'] ?? $_SESSION['user']['id'];
        
        // Solo admin puede ver registros de otros usuarios
        if ((int)$userId !== (int)$_SESSION['user']['id'] && ($_SESSION['user']['cargo'] ?? '') !== 'admin') {
            http_response_code(403);
            echo json_encode(['error'=>'No autorizado para ver registros de otro usuario']);
            exit;
        }
        
        $stmt = $this->pdo->prepare('SELECT * FROM registros WHERE user_id = ? ORDER BY fecha ASC');
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll();
        echo json_encode(['data'=>$rows]);
    }

    public function getAll(){
        // for jefe view - returns registros grouped by user
        $stmt = $this->pdo->query('SELECT r.*, u.nombres, u.apellidos, u.unidad_operativa FROM registros r JOIN users u ON u.id=r.user_id ORDER BY u.nombres');
        $rows = $stmt->fetchAll();
        echo json_encode(['data'=>$rows]);
    }

    public function update(){
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['user'])) { http_response_code(401); echo json_encode(['error'=>'No autorizado']); exit; }
        
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) { http_response_code(400); echo json_encode(['error'=>'No data']); exit; }
        
        $id = $data['id'] ?? null;
        if (!$id) { http_response_code(400); echo json_encode(['error'=>'Falta id']); exit; }
        
        // Campos permitidos para actualizar
        $fields = ['hora_entrada','hora_salida','toneladas','ton_coteadas','bono_transporte','bono_alimentacion','recargo_nocturno','he_diurnas','he_nocturnas','he_dom_fest'];
        $updates = [];
        $params = [];
        
        foreach ($fields as $f) {
            if (isset($data[$f])) {
                $updates[] = "$f = ?";
                $params[] = $data[$f];
            }
        }
        
        if (empty($updates)) { echo json_encode(['success'=>true,'message'=>'Nada para actualizar']); exit; }
        
        $params[] = $id;
        $sql = 'UPDATE registros SET ' . implode(', ', $updates) . ' WHERE id = ?';
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
