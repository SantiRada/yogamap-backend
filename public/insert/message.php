<?php
require_once '../../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $message = isset($input['message']) ? $input['message'] : null;

    if (empty($message)) {
        echo json_encode(["success" => false, "message" => "Faltan datos para crear el mensaje..."]);
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO messages (idgroup,iduser,content,time,fijado) VALUES (:idgroup,:iduser,:content,:time,:pin)");
        $stmt->bindParam(':idgroup', $message['idgroup']);
        $stmt->bindParam(':iduser', $message['iduser']);
        $stmt->bindParam(':content', $message['content']);
        $stmt->bindParam(':time', $message['time']);
        $stmt->bindParam(':pin', $message['pin']);
        if($stmt->execute()){ echo json_encode(["success" => true, "message" => "Mensaje creado con éxito..." ]); }
        else { echo json_encode(["success" => false, "message" => "Error al crear el mensaje" ]); }

    } catch (PDOException $e) { echo json_encode(["success" => false, "message" => "Error de base de datos."]); }
} else {
    echo json_encode(["success" => false, "message" => "Método no permitido."]);
}
?>
