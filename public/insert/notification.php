<?php
require_once '../../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    if ($input === null) { 
        echo json_encode(["success" => false, "message" => "JSON inválido."]); 
        exit(); 
    }
    
    $idProf = isset($input['idProf']) ? trim($input['idProf']) : null;
    $idUser = isset($input['idUser']) ? trim($input['idUser']) : null;
    $type = isset($input['type']) ? trim($input['type']) : null;
    $data = isset($input['data']) ? $input['data'] : null;

    if (empty($idProf) || empty($idUser) || empty($type) || !is_array($data) || empty($data)) {
        echo json_encode(["success" => false, "message" => "Todos los campos son obligatorios."]);
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO notifications (iduser, viewed, title, content, idadmin, type) VALUES (:idUser, :viewed, :title, :content, :idAdmin, :type)");

        // Obtener información del profesor
        $profSql = $pdo->prepare("SELECT * FROM users WHERE idprof = :id");
        $profSql->bindParam(':id', $idProf);
        $profSql->execute();
        $prof = $profSql->fetch();

        // Obtener información del usuario
        $usersql = $pdo->prepare("SELECT name FROM users WHERE id = :id");
        $usersql->bindParam(':id', $idUser);
        $usersql->execute();
        $user = $usersql->fetch();

        if ($type == "clases") {
            if (!isset($data['typeYoga']) || !isset($data['horarios'])) {
                echo json_encode(["success" => false, "message" => "Falta la información de las clases"]);
                exit();
            }
            $title = "Solicitud de Clase de " . $user['name'];
            $content = "¡Felicidades " . $prof['name'] . "!\n\n";
            $content .= "Te informamos que el usuario " . $user['name'] . " ha solicitado clases de '" . $data['typeYoga'] . "'.\n";
            $content .= "En los siguientes horarios:\n\n";
            foreach($data['horarios'] as $time):
                $stmtimes = $pdo->prepare("SELECT * FROM horarios WHERE id = :id");
                $stmtimes->bindParam(':id', $time, PDO::PARAM_INT);
                $stmtimes->execute();
                $time = $stmtimes->fetch();
                $day = [ 0 => "Lunes", 1 => "Martes", 2 => "Miércoles", 3 => "Jueves", 4 => "Viernes", 5 => "Sábado", 6 => "Domingo" ];

                $content .= $day[$time['day']] . ": " . $time['horas'] . "\n";
            endforeach;
            $content .= "\nSi deseas aceptar esta solicitud, por favor haz clic en la opción a continuación para que " . $user['name'] . " reciba una notificación confirmando que puede asistir a tus clases.\n\n";
            $content .= "[CONFIRMAR]";
            $content .= "\n\n¡Gracias por tu atención!\n";
        } else {
            if (!isset($data['formacion'])) {
                echo json_encode(["success" => false, "message" => "Falta la información de las formaciones"]);
                exit();
            }
            $title = "Solicitud de Formación de " . $user['name'];
            $content = "¡Felicidades " . $prof['name'] . "!\n\n";
            $content .= "Te informamos que el usuario " . $user['name'] . " ha solicitado información sobre la formación '" . $data['formacion'] . "'.\n";
            $content .= "Si deseas contactar al usuario para brindarle más detalles o resolver sus dudas, puedes hacerlo a través de la opción de contacto de a continuación.\n\n";
            $content .= "[CONTACTAR_USUARIO]";
            $content .= "\n\n¡Gracias por tu atención!\n";
        }

        // Vincular los parámetros
        $idUserParam = $prof['id'];
        $viewedParam = 0;
        $titleParam = $title;
        $contentParam = $content;
        $idAdminParam = $idUser;
        $typeParam = $type;
        
        $stmt->bindParam(':idUser', $idUserParam, PDO::PARAM_INT);
        $stmt->bindParam(':viewed', $viewedParam, PDO::PARAM_INT);
        $stmt->bindParam(':title', $titleParam);
        $stmt->bindParam(':content', $contentParam);
        $stmt->bindParam(':idAdmin', $idAdminParam, PDO::PARAM_INT);
        $stmt->bindParam(':type', $typeParam);

        // Ejecutar la consulta
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Notificación creada con éxito."]);
        } else {
            echo json_encode(["success" => false, "message" => "Error al crear la notificación."]);
        }
    } catch (PDOException $e) {
        // En caso de error de base de datos, devolver una respuesta
        echo json_encode(["success" => false, "message" => "Error de base de datos."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Método no permitido."]);
}
?>