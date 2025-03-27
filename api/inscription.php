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

    $nomUtilisateur = $data['nom-utilisateur'];
    $courriel = $data['adresse-courriel'];
    $motDePasse = $data['mot-de-passe'];
    $confirmerMDP = $data['conf-mdp']; 

    $stmt = $pdo->prepare("SELECT courriel FROM utilisateurs WHERE courriel = ?");
    $stmt -> execute([$courriel]);
    $confirmerCourriel = $stmt -> fetch();

    if($confirmerCourriel){
        echo json_encode(["token" => false]);
    } else {
        if($motDePasse === $confirmerMDP){
        
        $MDPHache = password_hash($motDePasse, PASSWORD_DEFAULT);

        try{
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, courriel, mot_de_passe) VALUES (?, ?, ?)");
            $stmt -> execute([$nomUtilisateur, $courriel, $MDPHache]);

            $idUtilisateur = $pdo->lastInsertId();

            $stmt2 = $pdo->prepare("INSERT INTO calendriers (nom, auteur_id) VALUES (?, ?)");
            $stmt2->execute([$nomUtilisateur, $idUtilisateur]);

            $idCalendrier = $pdo->lastInsertId();

            $stmt3 = $pdo->prepare("INSERT INTO calendrier_utilisateur (user_id, id_calendrier, role) VALUES (?, ?, ?)");
            $stmt3->execute([$idUtilisateur, $idCalendrier, "AUTHOR"]);

            $pdo->commit();
            // TODO retourne seulement true pour l'instant modifier quand je vais recevoir le document.
            echo json_encode(["token" => true]);

        } catch( \Throwable $e){
            $pdo->rollback();
            echo json_encode(["token" => false]);
        }
    }
}
?>