<?php
require_once '../../src/db.php';

// startTime: horarioInicio,
// endTime: horarioFin,
// day: day,
// typeofyoga: select

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $id = isset($input['id']) ? trim($input['id']) : null;
    $startTime = isset($input['startTime']) ? trim($input['startTime']) : null;
    $endTime = isset($input['endTime']) ? trim($input['endTime']) : null;
    $day = isset($input['day']) ? trim($input['day']) : null;
    $typeofyoga = isset($input['typeofyoga']) ? trim($input['typeofyoga']) : null;

    try {
        $stmt = $pdo->prepare("INSERT INTO horarios (startTime,endTime,disponibilidad,day,typeofyoga,idprof) VALUES (:startTime,:endTime,'',:day,:typeofyoga,:id)");
        $stmt->bindParam(':startTime', $startTime);
        $stmt->bindParam(':endTime', $endTime);
        $stmt->bindParam(':day', $day);
        $stmt->bindParam(':typeofyoga', $typeofyoga);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        if($stmt->execute()):
            echo json_encode(["success" => true, "message" => "Horarios creados con éxito."]);
            exit;
        else:
            echo json_encode(["success" => false, "message" => "Falló la creación de horarios..." . implode(", ", $stmt->errorInfo())]);
            exit;
        endif;
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
        exit;
    }
} else {
    echo json_encode(["success" => false, "message" => "Método no permitido."]);
}
?>
