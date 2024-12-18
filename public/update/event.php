<?php
// Este script sirve para editar un evento específico según su ID
require_once '../../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? trim($_POST['id']) : null;
    $name = isset($_POST['name']) ? trim($_POST['name']) : null;
    $theme = isset($_POST['theme']) ? trim($_POST['theme']) : null;
    $description = isset($_POST['description']) ? trim($_POST['description']) : null;
    $horarios = isset($_POST['horarios']) ? trim($_POST['horarios']) : null;
    $ubication = isset($_POST['ubication']) ? trim($_POST['ubication']) : null;
    $image = isset($_FILES['image']) ? $_FILES['image'] : null;

    try {
        // Obtener la imagen anterior y el id del profesor (idorg) para la carpeta
        $stmt = $pdo->prepare("SELECT img, idorg FROM events WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $eventData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$eventData) {
            echo json_encode(["success" => false, "message" => "Evento no encontrado."]);
            exit;
        }

        $oldImage = "../../assets/events/" . $eventData['img'];
        $idorg = $eventData['idorg']; // ID del profesor para la carpeta

        // Actualizar los datos del evento en la base de datos
        $stmt = $pdo->prepare("UPDATE events SET name = :name, theme = :theme, description = :description, horarios = :horarios, ubication = :ubication WHERE id = :id");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':theme', $theme);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':horarios', $horarios);
        $stmt->bindParam(':ubication', $ubication);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Procesar la nueva imagen
        if ($image && $image['error'] == UPLOAD_ERR_OK) {
            $imageExtension = pathinfo($image['name'], PATHINFO_EXTENSION);
            $newImagePath = "../../assets/events/$idorg/event.$imageExtension"; // Carpeta usando idorg
            $newImageDatabasePath = "$idorg/event.$imageExtension"; // Carpeta usando idorg

            // Crear la carpeta si no existe
            if (!file_exists(dirname($newImagePath))) {
                mkdir(dirname($newImagePath), 0777, true);
            }

            // Mover la nueva imagen
            move_uploaded_file($image['tmp_name'], $newImagePath);

            // Actualizar la ruta de la imagen en la base de datos
            $stmt = $pdo->prepare("UPDATE events SET img = :img WHERE id = :id");
            $stmt->bindParam(':img', $newImageDatabasePath);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            // Eliminar la imagen anterior
            if ($oldImage && file_exists($oldImage)) { unlink($oldImage); }
        }

        echo json_encode(["success" => true, "message" => "Evento modificado con éxito."]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
}
