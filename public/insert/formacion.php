<?php
require_once '../../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? trim($_POST['id']) : null;
    $title = isset($_POST['title']) ? trim($_POST['title']) : null;
    $description = isset($_POST['description']) ? trim($_POST['description']) : null;
    $chips = isset($_POST['chips']) ? trim($_POST['chips']) : null;
    $image = isset($_FILES['image']) ? $_FILES['image'] : null;

    // Validaciones de datos
    if (empty($id) || empty($title) || empty($description) || empty($chips)):
        echo json_encode(["success" => false, "message" => "Faltan datos obligatorios en CreateForm: " . $id]);
        exit;
    endif;

    try {
        $stmt = $pdo->prepare("INSERT INTO formaciones (idprof,title,description,themes,img) VALUES (:idprof,:title,:desc,:themes,'')");
        $stmt->bindParam(':idprof', $id, PDO::PARAM_INT);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':desc', $description);
        $stmt->bindParam(':themes', $chips);
        
        if($stmt->execute()):
            #region ENCONTRAR EL ID DE LA ÚLTIMA FORMACION
            $stmtImg = $pdo->prepare("SELECT * FROM formaciones WHERE idprof = :idprof ORDER BY id DESC");
            $stmtImg->bindParam(':idprof', $id, PDO::PARAM_INT);
            $stmtImg->execute();
            $newFormation = $stmtImg->fetch(PDO::FETCH_ASSOC);
            $idFormation = $newFormation['id'];
            #endregion

            #region WORK IN IMAGE
            if ($image && $image['error'] == UPLOAD_ERR_OK) {
                $imageExtension = pathinfo($image['name'], PATHINFO_EXTENSION);
                $newImagePath = "../../assets/formaciones/$id/$idFormation.$imageExtension"; 
                $newImageDatabasePath = "$id/$idFormation.$imageExtension"; 
    
                if (!file_exists(dirname($newImagePath))) {
                    mkdir(dirname($newImagePath), 0777, true);
                }
    
                move_uploaded_file($image['tmp_name'], $newImagePath);
    
                $stmt = $pdo->prepare("UPDATE formaciones SET img = :img WHERE id = :id");
                $stmt->bindParam(':img', $newImageDatabasePath);
                $stmt->bindParam(':id', $idFormation, PDO::PARAM_INT);
                if($stmt->execute()){
                    echo json_encode(["success" => true, "message" => "Formación creada con éxito."]);
                    exit;
                }else{
                    echo json_encode(["success" => false, "message" => "Falló el guardado de la imagen de la formación." . implode(", ", $stmt->errorInfo())]);
                    exit;
                }
            } else {
                echo json_encode(["success" => false, "message" => "No se encontró la imagen de la formación." . implode(", ", $stmt->errorInfo())]);
                exit;
            }
            #endregion
        else:
            echo json_encode(["success" => false, "message" => "Falló la creación de la formación en datos: " . implode(", ", $stmt->errorInfo())]);
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
