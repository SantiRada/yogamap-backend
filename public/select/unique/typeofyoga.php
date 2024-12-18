<?php
// Este script sirve para seleccionar un TIPO DE YOGA en específico según su ID
require_once '../../../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $id = isset($input['id']) ? trim($input['id']) : null;

    if (empty($id)):
        echo json_encode(["success" => false, "message" => "El perfil que intentas buscar no existe o ha sido eliminado."]);
        exit();
    endif;

    try {
        $stmt = $pdo->prepare("SELECT * FROM typesofyoga WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $type = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!empty($type)) {
            echo json_encode(["success" => true, "message" => "Tipo de Yoga encontrado.", "type" => $type]);
        } else {
            echo json_encode(["success" => true, "message" => "No se ha encontrado el tipo de yoga."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Método no permitido."]);
}
