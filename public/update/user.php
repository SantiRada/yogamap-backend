<?php
// Este script sirve para editar un evento específico según su ID
require_once '../../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? trim($_POST['id']) : null;
    $pass = isset($_POST['pass']) ? trim($_POST['pass']) : null;
    $name = isset($_POST['name']) ? trim($_POST['name']) : null;
    $mail = isset($_POST['mail']) ? trim($_POST['mail']) : null;
    $newPass = isset($_POST['newPass']) ? trim($_POST['newPass']) : null;
    $newPassTwo = isset($_POST['newPassTwo']) ? trim($_POST['newPassTwo']) : null;

    try {
        // Buscar al usuario por ID
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (empty($user)) {
            echo json_encode(["success" => false, "message" => "Usuario '" . $id . "' no encontrado."]);
            exit;
        }

        // Verificar la contraseña ingresada con la almacenada
        if (!password_verify($pass, $user['pass'])) {
            echo json_encode(["success" => false, "warn" => "La contraseña es incorrecta."]);
            exit;
        }

        // Inicializar consulta para la actualización de datos
        $updateFields = [];
        if (!empty($name)) {
            $updateFields['name'] = $name;
        }
        if (!empty($mail)) {
            if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(["success" => false, "warn" => "El correo electrónico no tiene el formato adecuado."]);
                exit;
            }
            $updateFields['mail'] = $mail;
        }
        if (!empty($newPass)) {
            if ($newPass !== $newPassTwo) {
                echo json_encode(["success" => false, "warn" => "Las contraseñas nuevas no coinciden."]);
                exit;
            }
            $updateFields['pass'] = password_hash($newPass, PASSWORD_BCRYPT);
        }

        // Construir y ejecutar la consulta de actualización
        if (!empty($updateFields)) {
            $setClause = [];
            foreach ($updateFields as $column => $value) {
                $setClause[] = "$column = :$column";
            }
            $setClauseString = implode(", ", $setClause);
            $sql = "UPDATE users SET $setClauseString WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            foreach ($updateFields as $column => $value) {
                $stmt->bindValue(":$column", $value);
            }
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                echo json_encode(["success" => true, "message" => "Usuario modificado con éxito."]);
            } else {
                echo json_encode(["success" => false, "message" => "No fue posible modificar el usuario."]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "No se realizaron cambios."]);
        }

    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
}
