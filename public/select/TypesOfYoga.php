<?php
// Este script sirve para mostrar TYPES.OF.YOGA según un valor de COUNT
require_once '../../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $count = isset($input['count']) ? trim($input['count']) : null;

    if (empty($count)) {
        echo json_encode(["success" => false, "message" => "Debes específicar una cantidad para poder hacer la búsqueda."]);
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM typesofyoga ORDER BY id DESC LIMIT :count");
        $stmt->bindParam(':count', $count, PDO::PARAM_INT);
        
        $stmt->execute();

        $types = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($types)) {
            echo json_encode(["success" => true, "message" => "Eventos encontrados.", "types" => $types]);
        } else {
            echo json_encode(["success" => true, "message" => "No se han encontrado Tipos de Yoga."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Método no permitido."]);
}
