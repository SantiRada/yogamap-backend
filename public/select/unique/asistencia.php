
<?php
// Este script sirve para cambiar asistencias de un evento por ID
require_once '../../../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $id = isset($input['id']) ? trim($input['id']) : null; // ID del evento al que se marcará la asistencia
    $idUser = isset($input['idUser']) ? trim($input['idUser']) : null;

    if (empty($id) || empty($idUser)) {
        echo json_encode([
            "success" => false,
            "message" => "Faltan parámetros o el perfil que intentas buscar no existe."
        ]);
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM events WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $events = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($events) {
            $prevAsistencia = explode(',', $events['asistencia']);
            $asistencias = "";
            $encontrado = false;

            if(!empty($events['asistencia'])):
                foreach($prevAsistencia as $asist):
                    if($asist != $idUser):
                        // Cargar todos los usuarios que no sean el actual
                        $asistencias .= $asist . ",";
                    else:
                        // Marcar que el usuario DEJÓ de asistir
                        $encontrado = true;
                    endif;
                endforeach;

                if(!$encontrado): 
                    // Agregar el usuario a la lista de asistentes si no se encontró previamente
                    $asistencias .= $idUser;
                else:
                    $asistencias = substr($asistencias, 0, -1);
                endif;
            else:
                // AGREGAR ASISTENCIA ÚNICA
                $asistencias = $idUser;
            endif;

            $stmt = $pdo->prepare("UPDATE events SET asistencia = :assist where id = :id");
            $stmt->bindParam(':assist', $asistencias);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            if($stmt->execute()):
                echo json_encode([ "success" => true, "message" => "Asistencias corregidas.", "assist" => $asistencias ]);
            else:
                echo json_encode([ "success" => false, "message" => "No se corrigieron las asistencias.", "assist" => $asistencias ]);
            endif;
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Evento no encontrado."
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            "success" => false,
            "message" => "Error: " . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "Método no permitido."
    ]);
}
