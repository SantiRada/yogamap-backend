<?php
// Este script sirve para seleccionar una LISTA de FORMACIONES según la búsqueda o el valor COUNT
require_once '../../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $id = isset($input['id']) ? trim($input['id']) : null;

    if (is_null($id)) {
        echo json_encode(["success" => false, "message" => "Faltan parámetros para la búsqueda de imágenes (id): " . $id]);
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM prof WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($images)) {
            $imagenes = [];
            if(!empty($images[0]['img'])): $imagenes = explode(',', $images[0]['img']); endif;

            for($i = 0; $i < count($imagenes); $i++):
                $imagenes[$i] = "http://192.168.100.2/API_Yogamap/assets/prof/" . $id . "/" . $imagenes[$i];
            endfor;

            echo json_encode(["success" => true, "message" => "Imágenes encontradas.", "images" => $imagenes]);
        } else {
            echo json_encode(["success" => true, "message" => "No se han encontrado imágenes."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Método no permitido."]);
}
