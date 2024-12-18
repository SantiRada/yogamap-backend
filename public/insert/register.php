<?php
require_once '../../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $mail = isset($input['mail']) ? trim($input['mail']) : null;
    $name = isset($input['name']) ? trim($input['name']) : null;
    $password = isset($input['pass']) ? trim($input['pass']) : null;
    $passtwo = isset($input['twoPass']) ? trim($input['twoPass']) : null;
    $type = isset($input['type']) ? trim($input['type']) : null;

    if (empty($mail) || empty($name) || empty($password) || empty($passtwo) || empty($type)) {
        echo json_encode(["success" => false, "message" => "Todos los campos son obligatorios."]);
        exit();
    }

    if($password != $passtwo){
        echo json_encode(["success" => false, "message" => "Las contraseñas no coinciden."]);
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    try {
        if($type == "users"):
            $stmt = $pdo->prepare("INSERT INTO users (mail, name, pass) VALUES (:mail, :name, :pass)");
        else:
            $stmt = $pdo->prepare("INSERT INTO prof (mail, name, pass) VALUES (:mail, :name, :pass)");
        endif;

        $stmt->bindParam(':mail', $mail);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':pass', $hashed_password);

        if ($stmt->execute()) {
            if($type == "prof"):
                $stmt = $pdo->prepare("SELECT * FROM prof WHERE mail = :mail AND pass = :pass");
                $stmt->bindParam(':mail', $mail);
                $stmt->bindParam(':pass', $hashed_password);
                $stmt->execute();
                $prof = $stmt->fetch();
                $idprof = $prof['id'];

                $stmt = $pdo->prepare("INSERT INTO users (mail, name, pass, idprof) VALUES (:mail, :name, :pass, :idprof)");
                $stmt->bindParam(':mail', $mail);
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':pass', $hashed_password);
                $stmt->bindParam(':idprof', $idprof);
                $stmt->execute();
            endif;
            
            $stmt = $pdo->prepare("SELECT * FROM users WHERE mail = :mail AND pass = :pass");
            $stmt->bindParam(':mail', $mail);
            $stmt->bindParam(':pass', $hashed_password);
            $stmt->execute();
            $user = $stmt->fetch();

            if(!empty($user['idprof'])):
                echo json_encode(["success" => true, "message" => "Usuario registrado correctamente.", "user" => $user, "prof" => $prof]);
            else:
                echo json_encode(["success" => true, "message" => "Usuario registrado correctamente.", "user" => $user]);
            endif;
        } else {
            echo json_encode(["success" => false, "message" => "Error al registrar el usuario."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Método no permitido."]);
}
?>
