<?php
require_once '../../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $id = isset($input['id']) ? trim($input['id']) : null;

    if (empty($id)):
        echo json_encode(["success" => false, "message" => "Faltan datos obligatorios."]);
        exit;
    endif;

    try {
        $stmt = $pdo->prepare("SELECT * FROM prof WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $namegroup = $user['name'];
        $description = "Comunidad de " . $namegroup . ".";
        $countmembers = 0;
        $type = 0;

        $stmt = $pdo->prepare("INSERT INTO chats (namegroup, description, countmembers, idorg, type) VALUES (:namegroup, :descrip, :countmembers, :idorg, :type)");
        $stmt->bindParam(':namegroup', $namegroup);
        $stmt->bindParam(':descrip', $description);
        $stmt->bindParam(':countmembers', $countmembers, PDO::PARAM_INT);
        $stmt->bindParam(':idorg', $id, PDO::PARAM_INT);
        $stmt->bindParam(':type', $type, PDO::PARAM_INT);
        
        if($stmt->execute()):
            $stmt = $pdo->prepare("SELECT * from chats WHERE idorg = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $community = $stmt->fetch(PDO::FETCH_ASSOC)['id'];

            $stmt = $pdo->prepare("UPDATE prof SET community = :community WHERE id = :id");
            $stmt->bindParam(':community', $community);
            $stmt->bindParam(':id', $id);
            if($stmt->execute()):
                echo json_encode(["success" => true, "message" => "Comunidad creada con éxito."]);
                exit;
            else:
                echo json_encode(["success" => false, "message" => "Falló la creación de la comunidad" ]);
                exit;
            endif;
        else:
            echo json_encode(["success" => false, "message" => "Falló la creación de la comunidad" ]);
            exit;
        endif;
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
        exit;
    }
} else {
    echo json_encode(["success" => false, "message" => "Método no permitido."]);
}
?>
