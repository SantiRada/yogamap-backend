<?php
// Este script sirve para el FEED de eventos.
// Enviando un id de profe se seleccionan los eventos de ese profe.
// Enviando un id (-1) se autoseleccionan los últimos eventos, la cantidad se envia en el valor "count"
require_once '../../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $id = isset($input['id']) ? trim($input['id']) : null;
    $count = isset($input['count']) ? trim($input['count']) : null;
    $search = isset($input['search']) ? trim($input['search']) : null;

    if(empty($search)){
        if (empty($id) && empty($count)) {
            echo json_encode(["success" => false, "message" => "El evento que intentas buscar no existe o ha sido cancelado."]);
            exit();
        }
    }

    try {
        if(!empty($search)){
            // BUSCAR POR TEXTO ESCRITO
            $stmt = $pdo->prepare("SELECT * FROM events WHERE name LIKE :search OR theme LIKE :search OR ubication LIKE :search");
            $search = "%{$search}%";
            $stmt->bindParam(':search', $search);
        }
        else if ($id == -1) {
            // BUSCAR CON CANTIDAD
            $stmt = $pdo->prepare("SELECT * FROM events ORDER BY id DESC LIMIT :count");
            $stmt->bindParam(':count', $count, PDO::PARAM_INT);
        } else {
            // BUSCAR EVENTOS DE UN PROFE/ESCUELA ESPECIFICÓ
            $stmt = $pdo->prepare("SELECT * FROM events WHERE idorg = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        }
        
        $stmt->execute();

        $events = $stmt->fetchAll(PDO::FETCH_ASSOC); // Obtiene todos los registros en un array

        $eventAll = []; // Inicializa un array vacío
        if(!empty($events)){
            foreach ($events as $ev) {
                $stmt = $pdo->prepare("SELECT * FROM prof WHERE id = :id");
                $stmt->bindParam(':id', $ev['idorg'], PDO::PARAM_INT);
                $stmt->execute();
                $prof = $stmt->fetch(PDO::FETCH_ASSOC);
    
                $eventAll[] = [ // Usa el operador [] para agregar el nuevo evento
                    "id" => $ev['id'],
                    "profId" => $prof['id'],
                    "imgProf" => $prof['icon'],
                    "nameProf" => $prof['name'],
                    "title" => substr($ev['name'], 0, 40),
                    "description" => substr($ev['description'], 0, 40),
                    "themes" => $ev['theme'],
                    "image" => $ev['img'],
                ];
            }
        }

        if (!empty($eventAll)) {
            echo json_encode(["success" => true, "message" => "Eventos encontrados.", "event" => $eventAll]);
        } else {
            echo json_encode(["success" => true, "message" => "No se han encontrado eventos.", "event" => []]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Método no permitido."]);
}
