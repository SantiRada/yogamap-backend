<?php
// Este script sirve para seleccionar todos los CHATS de un usuario por su ID
require_once '../../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $id = isset($input['idUser']) ? trim($input['idUser']) : null;

    if (empty($id)) {
        echo json_encode(["success" => false, "message" => "El usuario que intentas comprobar no existe o ha sido eliminado."]);
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $allChats = [];

        if(!empty($user['groups'])): $chat_ids = explode(',', $user['groups']); 
        else: $chat_ids = ""; endif;

        if(!empty($user['idprof'])):
            $stmt = $pdo->prepare("SELECT * FROM prof WHERE id = :id;");
            $stmt->bindParam(':id', $user['idprof']);
            $stmt->execute();
            $prof = $stmt->fetch(PDO::FETCH_ASSOC);
            $community = $prof['community'];

            $chat_ids .= ',' . $community;
            $idProf = $user['id'];
        else: $idProf = null;
        endif;

        $total_ids = explode(',', $chat_ids);
        $placeholders = implode(',', array_fill(0, count($total_ids), '?'));

        $query = "SELECT * FROM chats WHERE id IN ($placeholders)";
        $stmt = $pdo->prepare($query);
        $stmt->execute($total_ids);

        $chats = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if(!empty($chats)):
            foreach ($chats as $chat):
                $stmt = $pdo->prepare("SELECT * FROM prof WHERE id = :id");
                $stmt->bindParam(':id', $chat['idorg'], PDO::PARAM_INT);
                $stmt->execute();
                $prof = $stmt->fetch(PDO::FETCH_ASSOC);

                $stmt = $pdo->prepare("SELECT * FROM messages WHERE idgroup = :id ORDER BY id DESC LIMIT 1");
                $stmt->bindParam(':id', $chat['id'], PDO::PARAM_INT);
                $stmt->execute();
                $message = $stmt->fetch(PDO::FETCH_ASSOC);

                if(empty($message)):
                    $lastMessage = "No hay mensajes en la comunidad...";
                    $lastTime = "";
                else:
                    $lastMessage = substr($message['content'], 0, 30) . '...';
                    $time = explode(':', $message['time']);
                    $lastTime = $time[0] . ':' . $time[1];
                endif;

                $allChats[] = [
                    "id" => $chat['id'],
                    "name" => $chat['namegroup'],
                    "idProf" => $idProf,
                    "nameProf" => $prof['name'],
                    "icon" => $prof['icon'],
                    "countmembers" => $chat['countmembers'],
                    "description" => $chat['description'],
                    "type" => $chat['type'],
                    "lastMessage" => $lastMessage,
                    "lastTime" => $lastTime,
                ];
            endforeach;
        else:
            echo json_encode(["success" => true, "message" => "No se han encontrado conversaciones."]);
        endif;

        if (!empty($allChats)) {
            echo json_encode(["success" => true, "message" => "Conversaciones encontradas.", "chats" => $allChats]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Método no permitido."]);
}
