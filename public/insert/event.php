<?php
require_once '../../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['idorg']) ? trim($_POST['idorg']) : null;
    $name = isset($_POST['name']) ? trim($_POST['name']) : null;
    $theme = isset($_POST['theme']) ? trim($_POST['theme']) : null;
    $description = isset($_POST['description']) ? trim($_POST['description']) : null;
    $horarios = isset($_POST['horarios']) ? trim($_POST['horarios']) : null;
    $ubication = isset($_POST['ubication']) ? trim($_POST['ubication']) : null;
    $image = isset($_FILES['image']) ? $_FILES['image'] : null;

    // Validaciones de datos
    if (empty($id) || empty($name) || empty($theme) || empty($description) || empty($horarios)):
        echo json_encode(["success" => false, "message" => "Faltan datos obligatorios."]);
        exit;
    endif;

    try {
        $stmt = $pdo->prepare("INSERT INTO events (idorg, name, theme, description, horarios, ubication) VALUES (:idorg, :name, :theme, :description, :horarios, :ubication)");
        $stmt->bindParam(':idorg', $id, PDO::PARAM_INT);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':theme', $theme);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':horarios', $horarios);
        $stmt->bindParam(':ubication', $ubication);
        
        if($stmt->execute()):
            $eventId = $pdo->lastInsertId();  // Obtener el ID del último evento insertado

            if ($image && $image['error'] == UPLOAD_ERR_OK) {
                $imageExtension = pathinfo($image['name'], PATHINFO_EXTENSION);
                $newImagePath = "../../assets/events/$id/event.$imageExtension"; 
                $newImageDatabasePath = "$id/event.$imageExtension"; 
    
                if (!file_exists(dirname($newImagePath))) {
                    mkdir(dirname($newImagePath), 0777, true);
                }
    
                move_uploaded_file($image['tmp_name'], $newImagePath);
    
                $stmt = $pdo->prepare("UPDATE events SET img = :img WHERE id = :eventId");
                $stmt->bindParam(':img', $newImageDatabasePath);
                $stmt->bindParam(':eventId', $eventId);
                if($stmt->execute()){
                    echo json_encode(["success" => true, "message" => "Evento creado con éxito."]);
                    exit;
                }else{
                    echo json_encode(["success" => false, "message" => "Falló el guardado de la imagen del evento." . implode(", ", $stmt->errorInfo())]);
                    exit;
                }
            } else {
                echo json_encode(["success" => false, "message" => "No se encontró la imagen del evento." . implode(", ", $stmt->errorInfo())]);
                exit;
            }
        else:
            echo json_encode(["success" => false, "message" => "Falló la creación del evento en datos..." . implode(", ", $stmt->errorInfo())]);
            exit;
        endif;
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
        exit;
    }
} else {
    echo json_encode(["success" => false, "message" => "Método no permitido."]);
}
?>
