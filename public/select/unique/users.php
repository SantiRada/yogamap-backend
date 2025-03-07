<?php
// Este script sirve para seleccionar un USERS específico según su ID
require_once '../../../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $id = isset($input['id']) ? trim($input['id']) : null;
    $notification_token = isset($input['notification_token']) ? trim($input['notification_token']) : null; // Nuevo campo para el token

    if (empty($id)) {
        echo json_encode(["success" => false, "message" => "El perfil que intentas buscar no existe o ha sido eliminado."]);
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id and notification_token = :notification_token");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':notification_token', $notification_token, PDO::PARAM_INT);
        
        $stmt->execute();

        $users = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!empty($users)) {
            echo json_encode(["success" => true, "message" => "Perfil encontrado.", "users" => $users]);
        } else {
            echo json_encode(["success" => true, "message" => "No se ha encontrado el usuario."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Método no permitido."]);
}
