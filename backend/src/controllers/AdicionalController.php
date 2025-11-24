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

    // Nuevo: genera filas "adicionales" a partir de la tabla "registros"
    public function fromRegistros() {
        // Obtener datos agrupados de la tabla registros + users
        $sql = "
            SELECT 
                u.unidad_operativa,

                COALESCE(SUM(r.he_diurnas),0) AS he_diurnas,
                COALESCE(SUM(r.he_nocturnas),0) AS he_nocturnas,
                COALESCE(SUM(r.he_dom_fest),0) AS he_dom_fest,
                COALESCE(SUM(r.he_diurnas_dom),0) AS he_diurnas_dom,
                COALESCE(SUM(r.he_nocturnas_dom),0) AS he_nocturnas_dom,
                COALESCE(SUM(r.ton_coteadas),0) AS ton_coteadas

            FROM registros r
            INNER JOIN users u ON u.id = r.user_id
            GROUP BY u.unidad_operativa
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Transformar a formato esperado para la tabla adicionales
        $output = [];

        foreach ($rows as $row) {
            $unidad = $row['unidad_operativa'];

            $output[] = ["unidad_operativa" => $unidad, "servicio" => "HE Diurnas",              "cantidad" => (float)$row["he_diurnas"]];
            $output[] = ["unidad_operativa" => $unidad, "servicio" => "HE Nocturnas",            "cantidad" => (float)$row["he_nocturnas"]];
            $output[] = ["unidad_operativa" => $unidad, "servicio" => "H Dom/Fest",              "cantidad" => (float)$row["he_dom_fest"]];
            $output[] = ["unidad_operativa" => $unidad, "servicio" => "HE Diurnas Dom/Fest",     "cantidad" => (float)$row["he_diurnas_dom"]];
            $output[] = ["unidad_operativa" => $unidad, "servicio" => "HE Nocturnas Dom/Fest",   "cantidad" => (float)$row["he_nocturnas_dom"]];
            $output[] = ["unidad_operativa" => $unidad, "servicio" => "Ton Coteadas",            "cantidad" => (float)$row["ton_coteadas"]];
        }

        echo json_encode(["data" => $output]);
    }
}
