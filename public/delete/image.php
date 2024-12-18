<?php
require_once '../../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $id = isset($input['id']) ? trim($input['id']) : null;
    $img = isset($input['img']) ? trim($input['img']) : null;
    
    if (is_null($id) || is_null($img)) {
        echo json_encode(["success" => false, "message" => "Faltan datos para eliminar la imagen."]);
        exit();
    }

    $imgs = explode('/', $img);
    $img = $imgs[count($imgs) - 1];

    try {
        $stmt = $pdo->prepare("SELECT img FROM prof WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row):
            $imgColumn = $row['img'];

            $imagePath = '../../assets/prof/' . $id . '/' . $img;

            if(strpos($imgColumn, ',') !== false):
                $imagenes = explode(',', $imgColumn);

                $index = array_search($img, $imagenes);
                if($index !== false):
                    unset($imagenes[$index]);

                    $imagenesActualizadas = implode(',', $imagenes);

                    $updateStmt = $pdo->prepare("UPDATE prof SET img = :img WHERE id = :id");
                    $updateStmt->bindParam(':img', $imagenesActualizadas);
                    $updateStmt->bindParam(':id', $id, PDO::PARAM_INT);
                    if($updateStmt->execute()):
                        if(file_exists($imagePath)):
                            unlink($imagePath);
                        else:
                            echo json_encode(["success" => true, "message" => "Imagen \"eliminada\" pero sin encontrarse."]);
                            exit;
                        endif;

                        echo json_encode(["success" => true, "message" => "Imagen eliminada con éxito."]);
                        exit;
                    else:
                        echo json_encode(["success" => false, "message" => "Falló la eliminación de la imagen de la lista."]);
                        exit;
                    endif;
                else:
                    echo json_encode(["success" => false, "message" => "No se encontró la imagen en la lista."]);
                    exit;
                endif;
            else:
                if ($imgColumn == $img) {
                    $updateStmt = $pdo->prepare("UPDATE prof SET img = NULL WHERE id = :id");
                    $updateStmt->bindParam(':id', $id, PDO::PARAM_INT);
                    
                    if($updateStmt->execute()):
                        if(file_exists($imagePath)):
                            unlink($imagePath);
                        else:
                            echo json_encode(["success" => true, "message" => "Imagen \"eliminada\" pero sin encontrarse."]);
                            exit;
                        endif;
                        echo json_encode(["success" => true, "message" => "Imagen eliminada con éxito."]);
                        exit;
                    else:
                        echo json_encode(["success" => false, "message" => "Falló la eliminación de la imagen."]);
                        exit;
                    endif;
                } else {
                    echo json_encode(["success" => false, "message" => "No se encontró la imagen: (1)" . $imgColumn . " (2)" . $img]);
                    exit;
                }
            endif;
        else:
            echo json_encode(["success" => false, "message" => "No se encontró el profesor."]);
            exit;
        endif;

    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
} else { echo json_encode(["success" => false, "message" => "No se permite el método."]); }