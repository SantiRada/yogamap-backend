<?php
// Este script sirve para verificar si el usuario está en una comunidad e ingresarlo si es posible
require_once '../../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $idUser = isset($input['idUser']) ? trim($input['idUser']) : null;
    $idCom = isset($input['idCom']) ? trim($input['idCom']) : null;

    if (empty($idUser) || empty($idCom)) {
        echo json_encode(["success" => false, "message" => "Faltan parámetros obligatorios para la consulta por usuario."]);
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id;");
        $stmt->bindParam(':id', $idUser, PDO::PARAM_INT);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if($user):
            if (strpos($user['groups'], $idCom) !== false):            
                // INGRESAR SIN HACER NADA
                echo json_encode([ "success" => true, "message" => "Comunidad encontrada.", "community" => true ]);
                exit;
            else:
                echo json_encode([ "success" => true, "message" => "Comunidad encontrada.", "community" => false ]);
                exit;
            endif;
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
