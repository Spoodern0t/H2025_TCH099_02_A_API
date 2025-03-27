<?php

    include(__DIR__ . '/../config.php');

    header('Content-Type: application/json');

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

    $courriel = $data['adresse-courriel'];
    $motDePasse = $data['mot-de-passe'];

    $pdo->beginTransaction();
    $pdo->commit(); // TODO

    $stmt = $pdo->prepare("SELECT courriel, mot_de_passe, id, nom FROM utilisateurs WHERE courriel = ?");
    $stmt-> execute([$courriel]);
    $utilisateur = $stmt->fetch();

    // Verifier si les informations reçu sont les bonnes.
    // Envoie de JSON pour la verification bonne ou mouvaise.
    if($utilisateur){
        if($utilisateur && password_verify($motDePasse, $utilisateur['mot_de_passe'])) {
            
            // TODO retourne seulement true pour l'instant modifier quand je vais recevoir le document.
            echo json_encode(["token" => true]);
        } else {
            echo json_encode(["token" => false]); 
        }
    } else {
        echo json_encode(["token" => false]);
    }

?>