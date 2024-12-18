<?php
// Este script sirve para verificar si el usuario está en una comunidad e ingresarlo si es posible
require_once '../../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $idUser = isset($input['idUser']) ? trim($input['idUser']) : null;
    $idCom = isset($input['idCom']) ? trim($input['idCom']) : null;

    if (empty($idUser) || empty($idCom)) {
        echo json_encode(["success" => false, "message" => "Faltan parámetros obligatorios para la consulta por comunidad."]);
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id;");
        $stmt->bindParam(':id', $idUser, PDO::PARAM_INT);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if($user):
            // AGREGAR A LA COMUNIDAD
            $stmt = $pdo->prepare("UPDATE users SET `groups` = CASE WHEN `groups` = '' OR `groups` IS NULL THEN CAST(:com AS CHAR) ELSE CONCAT(`groups`, ',', CAST(:com AS CHAR)) END WHERE id = :id;");
            $stmt->bindParam(':com', $idCom, PDO::PARAM_STR);
            $stmt->bindParam(':id', $idUser, PDO::PARAM_INT);
            if($stmt->execute()):
                echo json_encode(["success" => true, "message" => "Agregado a la comunidad (" . $idCom . ")" ]);
            else:
                echo json_encode(["success" => false, "message" => "Falló la carga de la comunidad (" . $idCom . ")" ]);
            endif;
            exit;
        else:
            echo json_encode(["success" => false, "message" => "No se encontró al usuario de ID: " . $idUser]);
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
