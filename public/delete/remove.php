<?php
require_once '../../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $id = isset($input['id']) ? trim($input['id']) : null;
    $type = isset($input['type']) ? trim($input['type']) : null;

    if (empty($id) || empty($type)) {
        echo json_encode(["success" => false, "message" => "Faltan datos para poder eliminar."]);
        exit();
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM ".$type." WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        if($stmt->execute()) { echo json_encode(["success" => true, "message" => "Elemento eliminado con éxito."]); }
        else { echo json_encode(["success" => false, "message" => "Falló la conexión a la base de datos para eliminar elementos."]); }

    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
}