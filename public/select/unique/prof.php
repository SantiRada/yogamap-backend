<?php
// Este script sirve para seleccionar un PROFE específico según su ID [OOO] Una cantidad dictada de profes nuevos
require_once '../../../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $id = isset($input['id']) ? trim($input['id']) : null;
    $count = isset($input['count']) ? trim($input['count']) : null;

    if (empty($id) && empty($count)):
        echo json_encode(["success" => false, "message" => "El perfil que intentas buscar no existe o ha sido eliminado."]);
        exit;
    endif;

    try {
        if(!empty($id)):
            $stmt = $pdo->prepare("SELECT * FROM prof WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        elseif(!empty($count)):
            $stmt = $pdo->prepare("SELECT * FROM prof ORDER BY id DESC LIMIT :count");
            $stmt->bindParam(':count', $count, PDO::PARAM_INT);
        else:
            echo json_encode(["success" => false, "message" => "Faltan datos para poder hacer la búsqueda."]);
            exit;
        endif;
        
        $stmt->execute();
        $profs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Procesar cada perfil para obtener los tipos de yoga
        foreach ($profs as &$prof) {
            #region COMMUNITY
            $stmt = $pdo->prepare("SELECT type FROM chats WHERE idorg = :id");
            $stmt->bindParam(':id', $prof['id'], PDO::PARAM_INT);
            $stmt->execute();

            $chats = $stmt->fetch(PDO::FETCH_ASSOC);

            if($chats && !empty($chats)): $prof['typecommunity'] = $chats['type']; endif;
            #endregion

            #region DISPONIBILIDAD
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM horarios WHERE idprof = :id");
            $stmt->bindParam(':id', $prof['id']);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if($result && !empty($result)): $prof['clients'] = $result['count'] ?? 0; endif;
            #endregion

            #region TYPES-OF-YOGA
            // Extraer los IDs de typesofyoga
            if(!empty($prof['typesofyoga'])):
                $yoga_ids = explode(',', $prof['typesofyoga']); 

                // Consulta para obtener los nombres de los tipos de yoga
                $placeholders = implode(',', array_fill(0, count($yoga_ids), '?')); // Generar placeholders (?, ?, ?)
                $query = "SELECT name FROM typesofyoga WHERE id IN ($placeholders)";
                $stmtYoga = $pdo->prepare($query);
                $stmtYoga->execute($yoga_ids);

                // Obtener los nombres de los tipos de yoga
                $yoga_names = $stmtYoga->fetchAll(PDO::FETCH_COLUMN); // Obtener solo la columna 'name'

                // Reemplazar los IDs por los nombres en el array 'prof'
                $prof['typesofyoga'] = implode(', ', $yoga_names);
                $prof['typesofyoga'] = substr($prof['typesofyoga'], 0, 30) . '...';
                $prof['typesofyogacomplete'] = implode(', ', $yoga_names);
            endif;
            #endregion
        }

        if (!empty($profs)) {
            echo json_encode(["success" => true, "message" => "Perfil encontrado.", "prof" => $profs]);
        } else {
            echo json_encode(["success" => true, "message" => "No se han encontrado perfiles."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Método no permitido."]);
}
