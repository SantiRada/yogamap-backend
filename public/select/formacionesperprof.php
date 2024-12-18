<?php
require_once '../../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $id = isset($input['id']) ? trim($input['id']) : null;

    if (empty($id)) {
        echo json_encode(["success" => false, "message" => "Falta el ID del profesor: " . $id]);
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM formaciones WHERE idprof = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $form = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($form)) {
            echo json_encode(["success" => true, "message" => "Formaciones encontradas.", "formaciones" => $form]);
        } else {
            echo json_encode(["success" => false, "message" => "No se han encontrado Formaciones."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Método no permitido."]);
}
