<?php
// Este script sirve para seleccionar todos los CHATS de un usuario por su ID
require_once '../../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $id = isset($input['id']) ? trim($input['id']) : null;

    if (empty($id)) {
        echo json_encode(["success" => false, "message" => "La comunidad que intentas comprobar no existe o ha sido eliminada."]);
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM messages WHERE idgroup = :id ORDER BY id DESC");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $conversation = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $finalConversation = []; // Nuevo arreglo para almacenar los mensajes con la información del usuario

        foreach ($conversation as $chat) {
            // Obtener la información del usuario
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->bindParam(':id', $chat['iduser']);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            $finalConversation[] = [
                "id" => $chat['id'],
                "iduser" => $chat['iduser'],
                "name" => $user['name'],
                "icon" => $user['icon'],
                "idgroup" => $chat['idgroup'],
                "content" => $chat['content'],
                "time" => $chat['time'],
                "pin" => $chat['fijado'],
            ];
        }

        if (!empty($finalConversation)) {
            echo json_encode(["success" => true, "message" => "Conversaciones encontradas.", "conversation" => $finalConversation]);
        } else {
            echo json_encode(["success" => false, "message" => "No se encontraron conversaciones."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Método no permitido."]);
}
