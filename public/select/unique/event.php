<?php
// Este script sirve para seleccionar un evento específico según su ID
require_once '../../../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $id = isset($input['id']) ? trim($input['id']) : null;

    if (empty($id)) {
        echo json_encode(["success" => false, "message" => "El evento que intentas buscar no existe o ha sido cancelado."]);
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM events WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        $stmt->execute();

        $events = $stmt->fetchAll(PDO::FETCH_ASSOC); // Obtiene todos los registros en un array

        if (!empty($events)) {
            echo json_encode(["success" => true, "message" => "Eventos encontrados.", "event" => $events]);
        } else {
            echo json_encode(["success" => true, "message" => "No se han encontrado eventos."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Método no permitido."]);
}
