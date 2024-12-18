<?php
// Este script sirve para editar un evento específico según su ID
require_once '../../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $id = isset($input['id']) ? trim($input['id']) : null;
    $idUser = isset($input['idUser']) ? trim($input['idUser']) : null;
    $newcount = isset($input['newcount']) ? trim($input['newcount']) : null;

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(':id', $idUser, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        #region TRABAJAR LOS NUEVOS GRUPOS
        $groups = explode(',', $user['groups']);
        $newgroups = "";
        foreach($groups as $group): if($group != $id): $newgroups .= $group . ','; endif; endforeach;
        $newgroups = substr($newgroups, 0, -1);
        #endregion

        $stmt = $pdo->prepare("UPDATE users SET groups = :newgroup WHERE id = :id");
        $stmt->bindParam(':newgroup', $newgroups);
        $stmt->bindParam(':id', $idUser);

        if ($stmt->execute()) {
            $newcount = ($newcount - 1);
            $stmt = $pdo->prepare("UPDATE chats SET countmembers = :newcount WHERE id = :id");
            $stmt->bindParam(':newcount', $newcount);
            $stmt->bindParam(':id', $id);

            if($stmt->execute()){
                echo json_encode(["success" => true, "message" => "Edición exitosa."]);
            }else{
                echo json_encode(["success" => false, "message" => "Ha fallado la edición de la comunidad..."]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Ha fallado la edición del usuario..."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
}
