<?php
// Este script sirve para cambiar el espacio VIEWED de una notificación según su ID
require_once '../../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $id = isset($input['id']) ? trim($input['id']) : null;

    if (empty($id)):
        echo json_encode(["success" => false, "message" => "Es necesario un ID para modificar una notificación."]);
        exit();
    endif;

    try {
        $viewed = 1;
        $stmt = $pdo->prepare("UPDATE notifications SET viewed = :viewed WHERE id = :id");
        $stmt->bindParam(':viewed', $viewed);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Notificación modificada."]);
        } else {
            echo json_encode(["success" => false, "message" => "No se ha actualizado la notificación."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Método no permitido."]);
}
