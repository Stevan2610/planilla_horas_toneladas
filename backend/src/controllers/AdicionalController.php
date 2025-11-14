<?php
class AdicionalController {
    private $pdo;
    public function __construct($pdo){ $this->pdo = $pdo; }

    public function getAll(){
        $stmt = $this->pdo->query('SELECT * FROM adicionales ORDER BY unidad_operativa');
        echo json_encode(['data'=>$stmt->fetchAll()]);
    }

    public function save(){
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) { http_response_code(400); echo json_encode(['error'=>'No data']); exit; }
        // expect array of rows
        $this->pdo->beginTransaction();
        $this->pdo->query('TRUNCATE TABLE adicionales');
        $stmt = $this->pdo->prepare('INSERT INTO adicionales (unidad_operativa, servicio, cantidad, valor_unitario, valor_total) VALUES (?,?,?,?,?)');
        foreach ($data as $row){
            $valor_total = ($row['cantidad'] ?? 0) * ($row['valor_unitario'] ?? 0);
            $stmt->execute([$row['unidad_operativa'], $row['servicio'], $row['cantidad'], $row['valor_unitario'], $valor_total]);
        }
        $this->pdo->commit();
        echo json_encode(['success'=>true]);
    }
}
