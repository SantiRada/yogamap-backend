<?php
    // Este script sirve para seleccionar TODOS los HORARIOS de un PROFE según su ID
    require_once '../../src/db.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST'):
        $input = json_decode(file_get_contents("php://input"), true);
        $id = isset($input['id']) ? trim($input['id']) : null;

        if (empty($id)):
            echo json_encode(["success" => false, "message" => "Faltan parámetros: " . $id]);
            exit();
        endif;

        // Consulta para obtener los horarios organizados por día
        $stmtHorarios = $pdo->prepare("SELECT * FROM horarios WHERE idprof = :idprof ORDER BY day ASC");
        $stmtHorarios->bindParam(':idprof', $id, PDO::PARAM_INT);
        $stmtHorarios->execute();
        $resultHorarios = $stmtHorarios->fetchAll(PDO::FETCH_ASSOC);

        $newHorarios = [];

        if(!empty($resultHorarios)):
            foreach ($resultHorarios as $row) {
                $stmtyoga = $pdo->prepare("SELECT * FROM typesofyoga WHERE id = :id;");
                $stmtyoga->bindParam(':id', $row['typeofyoga'], PDO::PARAM_INT);
                $stmtyoga->execute();
                $types = $stmtyoga->fetch(PDO::FETCH_ASSOC);

                $stime = explode(':', $row['startTime']);
                $startTime = $stime[0] . ':' . $stime[1];
                
                $etime = explode(':', $row['endTime']);
                $endTime = $etime[0] . ':' . $etime[1];

                $newHorarios[] = [
                    "id" => $row['id'],
                    "idprof" => $row['idprof'],
                    "typeofyoga" => $row['typeofyoga'],
                    "typeofyogaNAME" => $types['name'],
                    "day" => $row['day'],
                    "horas" => $startTime . " a " . $endTime,
                    "disponibilidad" => $row['disponibilidad'],
                ];
            }

            if(!empty($newHorarios)):
                echo json_encode(["success" => true, "message" => "horarios encontrados.", "horarios" => $newHorarios]);
                exit;
            else:
                echo json_encode(["success" => false, "message" => "No se pudieron cargar los horarios."]);
                exit;
            endif;
        else:
            echo json_encode(["success" => false, "message" => "Este profe no cuenta con horarios disponibles."]);
            exit;
        endif;
    else: 
        echo json_encode(["success" => false, "message" => "Falló la carga de archivos de horario"]);
        exit;
    endif;
?>
