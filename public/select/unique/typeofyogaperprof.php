<?php
require_once '../../../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $id = isset($input['id']) ? trim($input['id']) : null;

    try {
        $stmt = $pdo->prepare("SELECT * FROM prof WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $prof = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!empty($prof)):
            $list = explode(',', $prof['typesofyoga']);
            $placeholders = implode(',', array_fill(0, count($list), '?'));
            $stmt = $pdo->prepare("SELECT * FROM typesofyoga WHERE id IN ($placeholders)");
            $stmt->execute($list);
            $types = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($types)) {
                echo json_encode(["success" => true, "message" => "Tipos de Yoga encontrados.", "types" => $types]);
                exit;
            } else {
                echo json_encode(["success" => true, "message" => "No se ha encontrado ningun tipo de yoga."]);
                exit;
            }
        else:
            echo json_encode(["success" => false, "message" => "No se ha encontrado el perfil del profesor."]);
            exit;
        endif;
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
        exit;
    }
} else {
    echo json_encode(["success" => false, "message" => "Método no permitido."]);
    exit;
}
