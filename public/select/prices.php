<?php
    // Este script sirve para seleccionar TODOS los PRECIOS de un PROFE según su ID
    require_once '../../src/db.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST'):
        $input = json_decode(file_get_contents("php://input"), true);
        $id = isset($input['id']) ? trim($input['id']) : null;

        if (empty($id)):
            echo json_encode(["success" => false, "message" => "Faltan parámetros (id): " . $id]);
            exit();
        endif;

        // Consulta para obtener los horarios organizados por día
        $stmt = $pdo->prepare("SELECT * FROM prices WHERE idprof = :id ORDER BY typeofyoga ASC");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $prices = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $newPrices = [];

        if(!empty($prices)):
            foreach ($prices as $row) {
                $stmtyoga = $pdo->prepare("SELECT * FROM typesofyoga WHERE id = :id;");
                $stmtyoga->bindParam(':id', $row['typeofyoga'], PDO::PARAM_INT);
                $stmtyoga->execute();
                $types = $stmtyoga->fetch(PDO::FETCH_ASSOC);

                $newPrices[] = [
                    "id" => $row['id'],
                    "idprof" => $row['idprof'],
                    "typeofyoga" => $row['typeofyoga'],
                    "typeofyogaNAME" => $types['name'],
                    "count" => $row['count'],
                    "price" => $row['price'],
                ];
            }

            if(!empty($newPrices)):
                echo json_encode(["success" => true, "message" => "Precios encontrados.", "prices" => $newPrices]);
                exit;
            else:
                echo json_encode(["success" => false, "message" => "No se pudieron cargar los precios."]);
                exit;
            endif;
        else:
            echo json_encode(["success" => false, "message" => "Este profe no cuenta con precios cargados."]);
            exit;
        endif;
    else: 
        echo json_encode(["success" => false, "message" => "Falló la carga de archivos de precios."]);
        exit;
    endif;
?>
