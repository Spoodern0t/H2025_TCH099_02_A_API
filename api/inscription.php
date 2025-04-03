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

    $nomUtilisateur = $data['user-name'];
    $courriel = $data['email'];
    $motDePasse = $data['password'];

    $stmt = $pdo->prepare("SELECT courriel FROM Utilisateur WHERE courriel = ?");
    $stmt -> execute([$courriel]);
    $confirmerCourriel = $stmt -> fetch();

    if($confirmerCourriel){
        echo json_encode(["token" => false]);
    } else {
        $MDPHache = password_hash($motDePasse, PASSWORD_DEFAULT);

        try{
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO Utilisateur (nom, courriel, mot_de_passe) VALUES (?, ?, ?)");
            $stmt -> execute([$nomUtilisateur, $courriel, $MDPHache]);

            $idUtilisateur = $pdo->lastInsertId();

            $stmt2 = $pdo->prepare("INSERT INTO Calendrier (nom, auteur_id) VALUES (?, ?)");
            $stmt2->execute([$nomUtilisateur, $idUtilisateur]);

            $idCalendrier = $pdo->lastInsertId();

            $stmt3 = $pdo->prepare("INSERT INTO utilisateur_calendrier (id_utilisateur, id_calendrier, est_membre) VALUES (?, ?, ?)");
            $stmt3->execute([$idUtilisateur, $idCalendrier, true]);

            $pdo->commit();
            // TODO retourne seulement true pour l'instant modifier quand je vais recevoir le document.
            http_response_code(200);

        } catch( \Throwable $e){
            $pdo->rollback();
            echo json_encode(["token" => false]);
        }
}
?>