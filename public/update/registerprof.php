<?php
// Este script sirve para GUARDAR toda la información de Registro de un Profe por su ID
require_once '../../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $id = isset($input['id']) ? trim($input['id']) : null;
    $typeAccount = isset($input['typeAccount']) ? trim($input['typeAccount']) : null;
    $typeYoga = isset($input['typeYoga']) ? trim($input['typeYoga']) : null;
    $typeAlumn = isset($input['typeAlumn']) ? trim($input['typeAlumn']) : null;
    $image = isset($input['image']) ? trim($input['image']) : null;
    $certificate = isset($input['certificate']) ? trim($input['certificate']) : null;
    $pay = isset($input['pay']) ? trim($input['pay']) : null;
    $community = isset($input['community']) ? trim($input['community']) : null;
    $ubication = isset($input['ubication']) ? trim($input['ubication']) : null;
    $notificationToken = isset($input['notificationToken']) ? trim($input['notificationToken']) : null;

    if (empty($id)) {
        echo json_encode(["success" => false, "message" => "El perfil del profe que intentas modificar no existe o ha sido eliminado."]);
        exit();
    }

    try {
        // Actualizar la información del usuario en la base de datos
        $stmt = $pdo->prepare("
            UPDATE prof 
            SET typeaccount = :typeAccount, 
                typesofyoga = :typeYoga, 
                typesalumn = :typeAlumn, 
                icon = :image, 
                matricula = :certificate, 
                metododepago = :pay, 
                community = :community, 
                ubication = :ubication, 
                notification_token = :notificationToken 
            WHERE id = :id
        ");
        $stmt->bindParam(':typeAccount', $typeAccount);
        $stmt->bindParam(':typeYoga', $typeYoga);
        $stmt->bindParam(':typeAlumn', $typeAlumn);
        $stmt->bindParam(':image', $image);
        $stmt->bindParam(':certificate', $certificate);
        $stmt->bindParam(':pay', $pay);
        $stmt->bindParam(':community', $community);
        $stmt->bindParam(':ubication', $ubication);
        $stmt->bindParam(':notificationToken', $notificationToken); // Puede ser NULL
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            echo json_encode(["success" => true, "message" => "Perfil del profe actualizado."]);
        } else {
            echo json_encode(["success" => false, "message" => "No se ha actualizado el perfil del profe..."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Método no permitido."]);
}
