<?php
require_once '../../src/db.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $content = isset($input['content']) ? trim($input['content']) : null;
    $time = isset($input['time']) ? trim($input['time']) : null;

    if (empty($content) || empty($time)) {
        echo json_encode(["success" => false, "message" => "Faltan datos o formato de tiempo incorrecto."]);
        exit();
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM messages WHERE content = :content AND DATE_FORMAT(time, '%H:%i') = :time");
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':time', $time);

        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            echo json_encode(["success" => true, "message" => "Elemento eliminado con éxito."]);
        } else {
            echo json_encode(["success" => false, "message" => "No se encontró ningún mensaje con esos datos."]);
        }

    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
}