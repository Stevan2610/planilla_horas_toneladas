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
        
        // Permitir que el propio usuario, el 'admin' o el 'jefe' vean registros de otros usuarios
        $role = $_SESSION['user']['cargo'] ?? '';
        if ((int)$userId !== (int)$_SESSION['user']['id'] && $role !== 'admin' && $role !== 'jefe') {
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

    public function getSummaryByUser(){
        // Retorna resumen de registros por auxiliar en un rango de fechas
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['user'])) { http_response_code(401); echo json_encode(['error'=>'No autorizado']); exit; }
        
        $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-t');
        
        // Validar formato de fechas
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaInicio) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaFin)) {
            http_response_code(400);
            echo json_encode(['error'=>'Formato de fecha inválido']);
            exit;
        }
        
        $stmt = $this->pdo->prepare('
            SELECT 
                u.id,
                u.nombres,
                u.apellidos,
                u.unidad_operativa,
                SUM(r.bono_transporte) as bono_transporte,
                SUM(r.bono_alimentacion) as bono_alimentacion,
                SUM(r.recargo_nocturno) as recargo_nocturno,
                SUM(r.he_diurnas) as he_diurnas,
                SUM(r.he_nocturnas) as he_nocturnas,
                SUM(r.he_dom_fest) as he_dom_fest,
                SUM(r.he_diurnas_dom) as he_diurnas_dom,
                SUM(r.he_nocturnas_dom) as he_nocturnas_dom,
                SUM(r.toneladas) as toneladas,
                SUM(r.ton_coteadas) as ton_coteadas,
                COUNT(r.id) as registros_count
            FROM users u
            LEFT JOIN registros r ON u.id = r.user_id AND r.fecha BETWEEN ? AND ?
            WHERE u.cargo = "auxiliar"
            GROUP BY u.id, u.nombres, u.apellidos, u.unidad_operativa
            ORDER BY u.nombres
        ');
        
        try {
            $stmt->execute([$fechaInicio, $fechaFin]);
            $rows = $stmt->fetchAll();
            echo json_encode(['data'=>$rows, 'fecha_inicio'=>$fechaInicio, 'fecha_fin'=>$fechaFin]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error'=>'Error al obtener resumen', 'message'=>$e->getMessage()]);
        }
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
