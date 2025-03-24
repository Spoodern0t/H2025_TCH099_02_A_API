<?php

    header('Content-Type: application/json');;

    $data = json_decode(file_get_contents("php://input"), true);

    // Verifier si les données ont été bien décoder
    if(json_last_error() !== JSON_ERROR_NONE){
        http_response_code(400);
        echo "Erreur du JSON : " . json_last_error_msg();
        exit;
    }

    // Verifier le format du tableau $data
    if(!is_array($data)){
        echo "Erreur du tableau";
        exit;
    }

    include '../config.php';

    $courriel = $data['courriel'];
    $motDePasse = $data['mot-de-passe'];

    $stmt = $pdo->prepare("SELECT courriel, mot_de_passe FROM utilisateurs WHERE courriel = ?");
    $stmt-> execute([$courriel]);

    $utilisateur = $stmt->fetch();

    // Verifier si les informations reçu sont les bonnes.
    // Envoie de JSON pour la verification bonne ou mouvaise.
    if($utilisateur){
        if($utilisateur && password_verify($motDePasse, $utilisateur['mot_de_passe'])) {
            echo json_encode(["statut" => true]);
        } else {
            echo json_encode(["statut" => false , "message" => "Mot de passe incorrect"]); 
        }
    } else {
        echo json_encode(["statut" => false, "message" => "Utilisateur incorrect"]);
    }

?>