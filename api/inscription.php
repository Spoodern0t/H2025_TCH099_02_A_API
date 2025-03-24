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

    $nomUtilisateur = $data['nom-utilisateur'];
    $courriel = $data['adresse-courriel'];
    $motDePasse = $data['mot-de-passe'];
    $confirmerMDP = $data['conf-mdp']; 

    $stmt = $pdo->prepare("SELECT courriel FROM utilisateurs WHERE courriel = ?");
    $stmt -> execute([$courriel]);
    $confirmerCourriel = $stmt -> fetch();

    if($confirmerCourriel){
        echo json_encode(["statut" => false , "message" => "Email invalide"]);
    } else {
        if($motDePasse === $confirmerMDP){
        
        $MDPHache = password_hash($motDePasse, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, courriel, mot_de_passe) VALUES (?, ?, ?)");
        $stmt -> execute([$nomUtilisateur, $courriel, $MDPHache]);

        $idUtilisateur = $pdo->lastInsertId();

        $stmt = $pdo->prepare("INSERT INTO calendriers (nom, auteur_id) VALUES (?, ?)");
        $stmt->execute([$nomUtilisateur, $idUtilisateur]);

        $idCalendrier = $pdo->lastInsertId();

        $stmt = $pdo->prepare("INSERT INTO calendrier_utilisateur (user_id, calendar_id, role) VALUES (?, ?, ?)");
        $stmt->execute([$idUtilisateur, $idCalendrier, "Auteur"]);
        echo json_encode(["statut" => true]);

        } else{
            echo json_encode(["statut" => false, "message" => "Mot de passe non similaire"]);
        }
    }
?>