<?php

// Este script sirve para editar un evento específico según su ID
require_once '../../../src/db.php';
$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario
    $id = $_POST['id'] ?? null;
    $name = $_POST['name'] ?? null;
    $mail = $_POST['mail'] ?? null;
    $typesofyoga = $_POST['typesofyoga'] ?? null;
    $typesalumn = $_POST['typesalumn'] ?? null;

    // Procesar la imagen si se ha subido
    if (isset($_FILES['icon'])) {
        $icon = $_FILES['icon'];

        // Validar la imagen
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($icon['type'], $allowedTypes)) {
            $uploadDir = '../uploads/'; // Carpeta de destino
            $uploadFile = $uploadDir . basename($icon['name']);

            // Mover el archivo subido a la carpeta de destino
            if (move_uploaded_file($icon['tmp_name'], $uploadFile)) {
                $iconPath = $uploadFile; // Ruta de la imagen
            } else {
                $response['success'] = false;
                $response['message'] = 'Error al mover la imagen.';
                echo json_encode($response);
                exit;
            }
        } else {
            $response['success'] = false;
            $response['message'] = 'Tipo de archivo no permitido.';
            echo json_encode($response);
            exit;
        }
    } else {
        $iconPath = null; // Sin imagen
    }

    // Actualizar datos en la base de datos
    $sql = "UPDATE profesores SET name = ?, mail = ?, typesofyoga = ?, typealumn = ?" . ($iconPath ? ", icon = ?" : "") . " WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    // Asignar parámetros
    if ($iconPath) {
        $stmt->bind_param("ssssi", $name, $mail, $typesofyoga, $typesalumn, $iconPath, $id);
    } else {
        $stmt->bind_param("sssi", $name, $mail, $typesofyoga, $typesalumn, $id);
    }

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Datos personales actualizados correctamente.';
    } else {
        $response['success'] = false;
        $response['message'] = 'Error al actualizar los datos: ' . $stmt->error;
    }

    // Cerrar la declaración y la conexión
    $stmt->close();
    $conn->close();
} else {
    $response['success'] = false;
    $response['message'] = 'Método no permitido.';
}

echo json_encode($response);
?>
