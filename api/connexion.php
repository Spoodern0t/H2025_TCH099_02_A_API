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

    $courriel = $data['email'];
    $motDePasse = $data['password'];

    $stmt = $pdo->prepare("SELECT courriel, mot_de_passe, id_utilisateur, nom FROM Utilisateur WHERE courriel = ?");
    $stmt-> execute([$courriel]);
    $utilisateur = $stmt->fetch();

    // Verifier si les informations reçu sont les bonnes.
    // Envoie de JSON pour la verification bonne ou mouvaise.
    if($utilisateur){
        if($utilisateur && password_verify($motDePasse, $utilisateur['mot_de_passe'])) {
            $key = constant('CLE_SECRETE');

            $payload = [
                "id_utilisateur" => $utilisateur['id_utilisateur'],
                "iat" => time(),
                "expiration" => time() + 1800
            ];

            $token = JWT::encode($payload, $key, 'HS256');

            $stmt = $pdo->prepare("SELECT id_utilisateur, id_calendrier, est_membre FROM Utilisateur_Calendrier WHERE id_utilisateur = ?");
            $stmt-> execute([$utilisateur['id_utilisateur']]);
            $calendrierUser = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $idCalendrierUser = array_column($calendrierUser, 'id_calendrier');
            $placeholders = count($idCalendrierUser) > 1 ? str_repeat('?,', count($idCalendrierUser) - 1). '?' : '?';

            $stmt = $pdo->prepare("SELECT auteur_id FROM Calendrier WHERE id_calendrier IN ($placeholders)");
            $stmt->execute($idCalendrierUser);
            $searchAuthor = $stmt->fetchAll(PDO:: FETCH_ASSOC);

            $idSearchAuthor = array_column($searchAuthor, 'auteur_id');
            $placeholders2 = count($idSearchAuthor) > 1 ? str_repeat('?,', count($idSearchAuthor) - 1). '?' : '?';

            $stmt = $pdo->prepare("SELECT nom FROM Utilisateur WHERE id_utilisateur IN ($placeholders2)");
            $stmt->execute($idSearchAuthor);
            $nomAuteur = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $userCalendars = [];

            $compteur = 0;
            foreach($calendrierUser as $calendrier){
                $userCalendars = [
                    "id_utilisateur" => $calendrier["id_utilisateur"],
                    "id_calendrier" => $calendrier["id_calendrier"],
                    "auteur" => $nomAuteur[$compteur]['nom'],
                    "est_membre" => (bool) $calendrier["est_membre"]
                ];
                $compteur++;
            }

            try{
                $pdo->beginTransaction();

                $stmt = $pdo->prepare("INSERT INTO connexion (token, id_utilisateur) VALUES (?, ?)");
                $stmt-> execute([$token, $utilisateur["id_utilisateur"]]);

                $pdo->commit();
            } catch (\Throwable $e){
                $pdo->rollBack();
                echo json_encode(["token" => false]);
            }

            echo json_encode
            ([
                "email" => $courriel,
                "username" => $utilisateur['nom'],
                "token" => $token,
                "userCalendars" => $userCalendars
                
            ]);

        } else {
            echo json_encode(["token" => false]); 
        }
    } else {
        echo json_encode(["token" => false]);
    }

?>