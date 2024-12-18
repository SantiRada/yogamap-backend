<?php
// Este script sirve para seleccionar TODOS los favoritos de un usuario por ID
require_once '../../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $id = isset($input['id']) ? trim($input['id']) : null;

    if (empty($id)) {
        echo json_encode([
            "success" => false,
            "message" => "Faltan parámetros o el perfil que intentas buscar no existe."
        ]);
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            echo json_encode([
                "success" => true,
                "message" => "Perfil encontrado.",
                "favs" => $user,
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Usuario no encontrado."
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            "success" => false,
            "message" => "Error: " . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "Método no permitido."
    ]);
}
