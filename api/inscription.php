<?php

    // 1) IL faut installer composer
    // 2) composer require firebase/php-jwt dans le fichier du projet
    // Inclure les choses si dessus pour charger la bibliothèque
    require_once __DIR__ . '/../vendor/autoload.php';
    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;

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

    include '../config.php';

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
        $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, courriel, mot_de_passe) VALUES (?, ?, ?)");
        $stmt -> execute([$nomUtilisateur, $courriel, $MDPHache]);

        $idUtilisateur = $pdo->lastInsertId();

        // A voir avec les gars
        $stmt = $pdo->prepare("INSERT INTO calendriers (nom, auteur_id) VALUES (?, ?)");
        $stmt->execute([$nomUtilisateur, $idUtilisateur]);

        $idCalendrier = $pdo->lastInsertId();

        // A voir avec les gars
        $stmt = $pdo->prepare("INSERT INTO calendrier_utilisateur (user_id, calendar_id, role) VALUES (?, ?, ?)");
        $stmt->execute([$idUtilisateur, $idCalendrier, "Auteur"]);
        
        // Création du token
        $key = bin2hex(random_bytes(32));
            // Le token expire 30 minutes après avoir été créer
            $tkRemis = time();
            $tkExpirer = $tkRemis + 1800;

            $payload = ["remit" => $tkRemis,
                        "expire" => $tkExpirer,
                        "token" => true, 
                        "user" => [ 
                            "courriel" => $courriel,
                            "motDePasse" => $motDePasse,
                            "nom" => $nomUtilisateur
                        ] 
                    ];

            $jwt = JWT::encode($payload, $key,'HS256');
            // Envoie du token sous la forme suivante : 
            /*
                {
                "token": true,
                "user": {
                    "id": "1",
                    "courriel": "user@example.com",
                    "nom": "John Doe"
                    }
                }
            */
            echo json_encode(["token" => true,
                              "user" => [
                                "id" => $idUtilisateur,
                                "courriel" => $courriel,
                                "nom" => $nomUtilisateur,
                                "password" => $motDePasse, 
                              ]
                            ]);

        } else{
            echo json_encode(["token" => false]);
        }
    }
?>