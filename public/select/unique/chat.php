<?php
// Este script sirve para seleccionar un CHAT específico según su ID
require_once '../../../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $id = isset($input['id']) ? trim($input['id']) : null;

    if (empty($id)) {
        echo json_encode(["success" => false, "message" => "El evento que intentas buscar no existe o ha sido cancelado."]);
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM chats WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $chat = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!empty($chat)) {
            $stmt = $pdo->prepare("SELECT * FROM prof WHERE id = :id");
            $stmt->bindParam(':id', $chat['idorg'], PDO::PARAM_INT);
            $stmt->execute();
            $prof = $stmt->fetch(PDO::FETCH_ASSOC);

            $chat = [
                "id" => $chat['id'],
                "name" => $chat['namegroup'],
                "description" => $chat['description'],
                "countmembers" => $chat['countmembers'],
                "idProf" => $chat['idorg'],
                "nameProf" => $prof['name'],
                "icon" => $prof['icon'],
                "type" => $chat['type'],
            ];

            echo json_encode(["success" => true, "message" => "Comunidad encontrada.", "chat" => $chat]);
        } else {
            echo json_encode(["success" => true, "message" => "No se ha encontrado la comunidad."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Método no permitido."]);
}
