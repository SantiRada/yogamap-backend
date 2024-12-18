<?php
require_once '../../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $mail = isset($input['mail']) ? trim($input['mail']) : null;
    $password = isset($input['pass']) ? trim($input['pass']) : null;
    $type = isset($input['type']) ? trim($input['type']) : null;

    if (empty($mail) || empty($password) || empty($type)) {
        echo json_encode(["success" => false, "message" => "Todos los campos son obligatorios."]);
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE mail = :mail");
        $stmt->bindParam(':mail', $mail);
        $stmt->execute();

        $user = $stmt->fetch();

        // VERIFICAR SI $user tiene resultados
        if ($user) {
            if($user['idprof'] != null):
                $stmt = $pdo->prepare("SELECT * FROM prof WHERE id = :id");
                $stmt->bindParam(':id', $user['idprof']);
                $stmt->execute();
                $prof = $stmt->fetch(PDO::FETCH_ASSOC);

                echo json_encode(["success" => true, "message" => "Login exitoso.", "user" => $user, "prof" => $prof]);
            else:
                echo json_encode(["success" => true, "message" => "Login exitoso.", "user" => $user]);
            endif;
        } else {
            echo json_encode(["success" => false, "message" => "Correo o contraseña incorrectos."]);
            exit();
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
        exit();
    }
} else { echo json_encode(["success" => false, "message" => "Método no permitido."]); }
