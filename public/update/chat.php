<?php
// Este script sirve para editar un evento específico según su ID
require_once '../../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $id = isset($input['id']) ? trim($input['id']) : null;
    $name = isset($input['name']) ? trim($input['name']) : null;
    $description = isset($input['description']) ? trim($input['description']) : null;
    $type = isset($input['type']) ? trim($input['type']) : null;

    try {
        $stmt = $pdo->prepare("UPDATE chats SET namegroup = :name, description = :description, type = :type WHERE id = :id");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "chat modificado con éxito."]);
        } else {
            echo json_encode(["success" => false, "message" => "No se ha actualizado el chat correctamente..."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
}
