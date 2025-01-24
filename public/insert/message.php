<?php
require_once '../../src/db.php';
// Importa la función sendNotification, asumiendo que está definida en otro archivo
require_once '../../src/sendNotifications.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $message = isset($input['message']) ? $input['message'] : null;

    if (empty($message)) {
        echo json_encode(["success" => false, "message" => "Faltan datos para crear el mensaje..."]);
        exit();
    }

    try {
        // Inserta el mensaje en la base de datos
        $stmt = $pdo->prepare("INSERT INTO messages (idgroup,iduser,content,time,fijado) VALUES (:idgroup,:iduser,:content,:time,:pin)");
        $stmt->bindParam(':idgroup', $message['idgroup']);
        $stmt->bindParam(':iduser', $message['iduser']);
        $stmt->bindParam(':content', $message['content']);
        $stmt->bindParam(':time', $message['time']);
        $stmt->bindParam(':pin', $message['pin']);
        
        if ($stmt->execute()) {
            // Obtiene el token de notificación del usuario desde la base de datos
            $stmtUser = $pdo->prepare("SELECT notification_token FROM users WHERE iduser = :iduser");
            $stmtUser->bindParam(':iduser', $message['iduser']);
            $stmtUser->execute();
            $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

            if ($user && isset($user['notification_token'])) {
                // Datos para la notificación
                $token = $user['notification_token'];
                $title = "Nuevo mensaje";
                $content = $message['content'];
                $data = [
                    "message_id" => $pdo->lastInsertId(),
                    "idgroup" => $message['idgroup']
                ];

                // Enviar notificación
                sendNotification($token, $title, $content, $data);

                echo json_encode(["success" => true, "message" => "Mensaje creado con éxito y notificación enviada."]);
            } else {
                echo json_encode(["success" => true, "message" => "Mensaje creado con éxito, pero no se encontró el token de notificación."]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Error al crear el mensaje"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Error de base de datos."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Método no permitido."]);
}
?>
