<?php
    require 'vendor/autoload.php';
    require './api/services/globalMethode.php';
    require './config/config.php';
    use \Firebase\JWT\JWT;
    use Firebase\JWT\Key;

    header('Content-Type: application/json');


    $data = json_decode(file_get_contents("php://input"), true);

    if(!isset($data['token'])) {
        http_response_code(401);
        // Pour faire des tests. Va etre enlever normalement
        echo json_encode(["message" => "Token manquant"]);
        exit;
    }

    $token = $data['token'];
    $key = constant('CLE_SECRETE');

    try{

        $decode = JWT::decode($token, new Key($key, 'HS256'));
        
        echo json_encode
        ([
            "message" => "Token valide",
            "utilisateur" => $decode->id_utilisateur ?? null,
            "exp" => $decode->exp ?? null
        ]);

    } catch(\Firebase\JWT\ExpiredException $e) {
        http_response_code(401);
        // Pour faire des tests. Va etre enlever normalement
        echo json_encode(["message" => "token expiré"]);
    } catch(Exception $e){
        // Pour faire des tests. Va etre enlever normalement
        http_response_code(401);
        // Pour faire des tests. Va etre enlever normalement
        echo json_encode(["message" => "token invalide", "Erreur" => $e->getMessage()]);
    }

?>