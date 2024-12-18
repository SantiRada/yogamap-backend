<?php
// Este script sirve para encontrar todas las notificaciones de un prof/user según tipo e id
require_once '../../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $id = isset($input['idUser']) ? trim($input['idUser']) : null;
    $type = isset($input['type']) ? trim($input['type']) : null;

    if (empty($id) || empty($type)) {
        echo json_encode(["success" => false, "message" => "Las notificaciones que intentas buscar no existen o han sido eliminadas."]);
        exit();
    }

    switch($type):
        case "1": $type = ""; break;
        case "2": $type = "clases"; break;
        case "3": $type = "formaciones"; break;
        default: 
            echo json_encode(["success" => false, "message" => "Tipo de notificación no válido."]);
            exit();
    endswitch;

    try {
        $stmt = $pdo->prepare("SELECT * FROM notifications WHERE iduser = :idUser AND type LIKE :type ORDER BY id DESC");
        $type = '%' . $type . '%';
        $stmt->bindParam(':idUser', $id, PDO::PARAM_INT);
        $stmt->bindParam(':type', $type);
        $stmt->execute();
        $notification = $stmt->fetchAll(PDO::FETCH_ASSOC); // Obtiene todos los registros en un array

        $allNotifications = []; // Inicializa un array vacío

        if (!empty($notification)) {
            foreach ($notification as $notif) {
                $stmt = $pdo->prepare("SELECT * FROM prof WHERE id = :id");
                $stmt->bindParam(':id', $notif['idadmin']);
                $stmt->execute();
                $prof = $stmt->fetch(PDO::FETCH_ASSOC);

                if($prof) {
                    $allNotifications[] = [
                        "id" => $notif['id'],
                        "iduser" => $notif['iduser'],
                        "idadmin" => $notif['idadmin'],
                        "title" => $notif['title'],
                        "content" => $notif['content'],
                        "viewed" => $notif['viewed'],
                        "img" => $prof['icon'],
                        "author" => $prof['name'],
                        "idProf" => $prof['id'],
                    ];
                } else {
                    $allNotifications[] = [
                        "id" => $notif['id'],
                        "iduser" => $notif['iduser'],
                        "idadmin" => $notif['idadmin'],
                        "title" => $notif['title'],
                        "content" => $notif['content'],
                        "viewed" => $notif['viewed'],
                        "img" => "http://192.168.100.2/API_Yogamap/assets/icon.png",
                        "author" => "Administración de Yogamap",
                        "idProf" => "-1",
                    ];
                }
            }
        }

        if (!empty($allNotifications)) {
            echo json_encode(["success" => true, "message" => "Notificaciones encontradas.", "notifications" => $allNotifications]);
        } else {
            echo json_encode(["success" => true, "message" => "No se han encontrado Notificaciones.", "notifications" => $allNotifications]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Método no permitido."]);
}
