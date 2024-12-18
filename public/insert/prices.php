<?php
require_once '../../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $id = isset($input['id']) ? trim($input['id']) : null;
    $typeofyoga = isset($input['typeofyoga']) ? trim($input['typeofyoga']) : null;
    $count = isset($input['count']) ? trim($input['count']) : null;
    $price = isset($input['price']) ? trim($input['price']) : null;

    try {
        $stmt = $pdo->prepare("INSERT INTO prices (idprof,typeofyoga,count,price) VALUES (:id,:typeofyoga,:count,:price)");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':typeofyoga', $typeofyoga, PDO::PARAM_INT);
        $stmt->bindParam(':count', $count, PDO::PARAM_INT);
        $stmt->bindParam(':price', $price);
        
        if($stmt->execute()):
            echo json_encode(["success" => true, "message" => "Precio creado con éxito."]);
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
