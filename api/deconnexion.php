<?php

    include(__DIR__ . '/../config.php');
    header('Content-Type: application/json');

    use Firebase\JWT\JWT;
    require 'vendor/autoload.php';

    $data = json_decode(file_get_contents("php://input"), true);

    // Verifier si les données ont été bien décoder
    if(json_last_error() !== JSON_ERROR_NONE){
        http_response_code(400);
        echo json_encode(["token" => false]);
        exit;
    }

    // Verifier le format du tableau $data
    if(!is_array($data)){
        echo json_encode(["token" => false]);
        exit;
    }

    $token = $data['token'];

    $stmt = $pdo-> prepare("DELETE FROM Connexion WHERE token = ?");
    $stmt-> execute([$token]);

    // COmment savoir que la ligne ai bien été supprimer avant de renvoyer une réponse positive ou négative
    
    if($stmt->rowCount() > 0){
        http_response_code(200);
    } else {
        echo json_encode(["token" => false]);
    }


?>