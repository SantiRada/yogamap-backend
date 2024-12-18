<?php
// Este script sirve para seleccionar un evento específico según su ID
require_once '../../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $id = isset($input['id']) ? trim($input['id']) : null;
    $idUser = isset($input['idUser']) ? trim($input['idUser']) : null;
    $type = isset($input['type']) ? trim($input['type']) : null;

    if (empty($id) || empty($type) || empty($idUser)) {
        echo json_encode(["success" => false, "message" => "El item que intentas modificar no existe o ha sido movido de lugar."]);
        exit();
    }

    if (!filter_var($id, FILTER_VALIDATE_INT) || !filter_var($idUser, FILTER_VALIDATE_INT)) {
        echo json_encode(["success" => false, "message" => "ID inválido."]);
        exit();
    }

    try {
        
        #region Verificar si está guardado y cambiarlo
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(':id', $idUser);
        $stmt->execute();
        $infoUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$infoUser) {
            echo json_encode(["success" => false, "message" => "Usuario no encontrado."]);
            exit();
        }

        if($type == "event"):
            $favCurrent = $infoUser['favEvent'] ?? "";
        elseif($type == "chat"):
            $favCurrent = $infoUser['favChat'] ?? "";
        else:
            $favCurrent = $infoUser['favProf'] ?? "";
        endif;

        $hasContent = false;
        $favNew = '';
        if(!empty($favCurrent)):
            $splitContent = explode(',', $favCurrent);

            foreach($splitContent as $content):
                if($content == $id):
                    $hasContent = true;
                else:
                    $favNew .= $content . ',';
                endif;
            endforeach;
            $favNew = substr($favNew, 0, -1);
        endif;

        if(!$hasContent):
            if(empty($favNew)): $favNew .= $id;
            else: $favNew .= ',' . $id;
            endif;
        endif;
        #endregion

        if($type == "event"):
            $stmt = $pdo->prepare("UPDATE users SET favEvent = :favs WHERE id = :id");
        elseif($type == "chat"):
            $stmt = $pdo->prepare("UPDATE users SET favChat = :favs WHERE id = :id");
        else:
            $stmt = $pdo->prepare("UPDATE users SET favProf = :favs WHERE id = :id");
        endif;
        $stmt->bindParam(':favs', $favNew);
        $stmt->bindParam(':id', $idUser);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            echo json_encode(["success" => true, "message" => "Favoritos modificados."]);
        } else {
            echo json_encode(["success" => false, "message" => "No se han actualizado los favoritos."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Método no permitido."]);
}
