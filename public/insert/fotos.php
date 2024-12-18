<?php
require_once '../../src/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = $_POST['id'] ?? null;

    $totalImages = [ ($_FILES['image-1'] ?? null), ($_FILES['image-2'] ?? null), ($_FILES['image-3'] ?? null), ($_FILES['image-4'] ?? null), ($_FILES['image-5'] ?? null), ($_FILES['image-6'] ?? null) ];

    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'ID de usuario no proporcionado.']);
        exit;
    }

    // Verifica si las imágenes fueron enviadas
    if (isset($_FILES['image-1'])) {
        $uploadDirectory = '../../assets/prof/' . $userId . '/'; // Ruta donde se guardarán las imágenes
        if (!is_dir($uploadDirectory)) {
            mkdir($uploadDirectory, 0777, true);
        }        

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB por imagen

        $uploadedFiles = [];
        $errors = [];

        foreach ($totalImages as $key => $image) {
            if(!is_null($image)):
                if ($image['error'] !== UPLOAD_ERR_OK) {
                    $errors[] = "Error en la carga de la imagen $key.";
                    continue;
                }

                // Verificar tipo y tamaño de la imagen
                if (!in_array($image['type'], $allowedTypes)) {
                    $errors[] = "El tipo de archivo no es válido para la imagen $key.";
                    continue;
                }

                if ($image['size'] > $maxSize) {
                    $errors[] = "La imagen $key excede el tamaño máximo permitido de 5MB.";
                    continue;
                }

                // Crear un nombre único para la imagen
                $uniqueName = uniqid('img_') . '.' . pathinfo($image['name'], PATHINFO_EXTENSION);
                $uploadPath = $uploadDirectory . $uniqueName;
                

                // Mover la imagen a la carpeta de uploads
                if (move_uploaded_file($image['tmp_name'], $uploadPath)) {
                    $uploadedFiles[] = $uniqueName;
                } else {
                    $errors[] = "No se pudo guardar la imagen $key.";
                }
            endif;
        }

        if (count($uploadedFiles) > 0) {
            $imagePaths = implode(',', $uploadedFiles);

            try {
                $stmt = $pdo->prepare("UPDATE prof SET img = :img WHERE id = :id");
                $stmt->bindParam(":img", $imagePaths);
                $stmt->bindParam(":id", $userId, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Imágenes cargadas con éxito.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'No se pudo guardar la información en la base de datos.']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'No se cargaron imágenes.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No se enviaron imágenes.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
}