<?php
// Este script sirve para seleccionar una LISTA de FORMACIONES según la búsqueda o el valor COUNT
require_once '../../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $search = isset($input['search']) ? trim($input['search']) : null;
    $count = isset($input['count']) ? trim($input['count']) : null;
    $stateTypeClass = isset($input['stateTypeClass']) ? trim($input['stateTypeClass']) : null;

    if (empty($count) && empty($stateTypeClass) && empty($search)) {
        echo json_encode(["success" => false, "message" => "No es posible cargar una lista de formaciones en este momento, intentalo de nuevo más tarde."]);
        exit();
    }

    try {
        if(!empty($search)):
            $search = '%' . $search . '%';
            if(empty($stateTypeClass)): $stmt = $pdo->prepare("SELECT * FROM formaciones WHERE title like :search OR ubication LIKE :search");
            else:
                if($stateTypeClass):
                    $stmt = $pdo->prepare("SELECT * FROM formaciones WHERE (title like :search OR ubication LIKE :search) AND ubication != ''");
                else:
                    $stmt = $pdo->prepare("SELECT * FROM formaciones WHERE (title like :search OR ubication LIKE :search) AND ubication = ''");
                endif;
            endif;
            
            $stmt->bindParam(':search', $search);
        else:
            $stmt = $pdo->prepare("SELECT * FROM formaciones ORDER BY id DESC LIMIT " . (int)$count);
        endif;
        
        $stmt->execute();

        $formaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $allFormation = [];
        if(count($formaciones) > 0){
            foreach ($formaciones as $form) {
                $allFormation[] = [
                    "id" => $form['id'],
                    "title" => substr($form['title'], 0, 40),
                    "description" => substr($form['description'], 0, 35) . '...',
                    "image" => $form['img'],
                ];
            }
        }

        if (!empty($allFormation)) {
            echo json_encode(["success" => true, "message" => "Formaciones encontradas.", "formaciones" => $allFormation]);
        } else {
            echo json_encode(["success" => true, "message" => "No se han encontrado Formaciones."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Método no permitido."]);
}
