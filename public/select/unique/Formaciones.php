<?php
// Este script sirve para seleccionar una formación en específico según su ID
require_once '../../../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $id = isset($input['id']) ? trim($input['id']) : null;

    if (empty($id)) {
        echo json_encode(["success" => false, "message" => "La formación que intentas buscar no existe o ha sido cancelada."]);
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM formaciones WHERE id = :id");
        $stmt->bindParam(':id', $id);
        
        $stmt->execute();

        $formaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($formaciones)) {
            echo json_encode(["success" => true, "message" => "Formaciones encontradas.", "formaciones" => $formaciones]);
        } else {
            echo json_encode(["success" => true, "message" => "No se han encontrado Formaciones."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Método no permitido."]);
}
