<?php
// Este script sirve para seleccionar la info de los favoritos de un usuario por ID
require_once '../../../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $id = isset($input['id']) ? trim($input['id']) : null;
    $idUser = isset($input['idUser']) ? trim($input['idUser']) : null;
    $type = isset($input['type']) ? trim($input['type']) : null;

    if (empty($id) || empty($idUser) || empty($type)) {
        echo json_encode(["success" => false, "message" => "Faltan parámetros o el perfil que intentas buscar no existe."]);
        exit();
    }

    try {
        // Consulta para obtener los datos del usuario
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :idUser");
        $stmt->bindParam(':idUser', $idUser);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Verificar si el usuario fue encontrado
        if (!$users) {
            echo json_encode(["success" => false, "message" => "Usuario no encontrado."]);
            exit();
        }

        // Obtener la columna correcta de favoritos según el tipo
        $favColumn = $type == "prof" ? 'favProf' : 'favEvent';
        $dataFav = isset($users[0][$favColumn]) ? $users[0][$favColumn] : '';

        // Comprobar si el id está en la lista de favoritos
        $favs = explode(',', $dataFav);
        $value = in_array($id, $favs);

        echo json_encode(["success" => true, "message" => "Perfil encontrado.", "fav" => $value]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Método no permitido."]);
}
