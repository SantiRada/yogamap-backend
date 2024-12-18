<?php
require_once '../../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $search = isset($input['search']) ? $input['search'] : null;
    $typesOfYoga = isset($input['typesOfYoga']) ? $input['typesOfYoga'] : null;
    $stateTypeClass = isset($input['stateTypeClass']) ? $input['stateTypeClass'] : null;

    try {
        $typesYoga = '';

        if ($search):
            $stmt = $pdo->prepare("SELECT id FROM typesofyoga WHERE name LIKE :search");
            $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
            $stmt->execute();
            $types = $stmt->fetchAll(PDO::FETCH_COLUMN);
            if (!empty($types)): $typesYoga = implode(',', $types); endif;
        endif;

        $sql = "SELECT * FROM prof WHERE name LIKE :search";
        if ($typesYoga): $sql .= " AND typesofyoga LIKE :types"; endif;
        if ($stateTypeClass === null): $sql .= " AND ubication IS NULL"; endif;

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        if ($typesYoga): $stmt->bindValue(':types', "%$typesYoga%", PDO::PARAM_STR); endif;
        $stmt->execute();
        $profs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($profs)): echo json_encode(["success" => true, "message" => "Profesores encontrados.", "profs" => $profs]);
        else: echo json_encode(["success" => true, "message" => "No se han encontrado profesores."]); endif;
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Método no permitido."]);
}