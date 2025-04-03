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

            $stmt = $pdo->prepare("SELECT id_utilisateur FROM Utilisateur WHERE courriel = ?");
            $stmt -> execute([$courriel]);
            $utilisateur = $stmt ->fetch(PDO::FETCH_ASSOC);

            $id_Utilisateur = $utilisateur['id_utilisateur'];

            $stmt2 = $pdo->prepare("INSERT INTO Calendrier (nom, auteur_id) VALUES (?, ?)");
            $stmt2->execute([$nomUtilisateur, $id_Utilisateur]);

            $stmt = $pdo->prepare("SELECT id_calendrier FROM Calendrier WHERE auteur_id = ?");
            $stmt -> execute([$id_Utilisateur]);
            $calendrier = $stmt ->fetch(PDO::FETCH_ASSOC);

            $id_Calendrier = $calendrier['id_calendrier'];

            try {
                $stmt = $pdo->prepare("INSERT INTO Utilisateur_Calendrier (id_utilisateur, id_calendrier, est_membre) VALUES (?, ?, ?)");
                $stmt->execute([$id_Utilisateur, $id_Calendrier, 1]);
            } catch (PDOException $e) {
                echo "Erreur d'insertion : " . $e->getMessage();
            }

            $pdo->commit();
            http_response_code(200);

        } catch( \Throwable $e){
            $pdo->rollback();
            echo json_encode(["token" => false]);
        }
}
?>