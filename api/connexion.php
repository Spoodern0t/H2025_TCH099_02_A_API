<?php

    // 1) IL faut installer composer
    // 2) composer require firebase/php-jwt dans le fichier du projet
    // Inclure les choses si dessus pour charger la bibliothèque 
    require_once __DIR__ . '/../vendor/autoload.php';
    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;

    header('Content-Type: application/json');;

    $data = json_decode(file_get_contents("php://input"), true);

    // Verifier si les données ont été bien décoder
    if(json_last_error() !== JSON_ERROR_NONE){
        http_response_code(400);
        echo json_encode(["token" => false, "message" => "Erreur JSON: " .json_last_error_msg()]);
        exit;
    }

    // Verifier le format du tableau $data
    if(!is_array($data)){
        echo json_encode(["token" => false, "message" => "Erreur de format du JSON"]);
        exit;
    }

    include '../config.php';

    $courriel = $data['courriel'];
    $motDePasse = $data['mot-de-passe'];

    $stmt = $pdo->prepare("SELECT courriel, mot_de_passe, id, nom FROM utilisateurs WHERE courriel = ?");
    $stmt-> execute([$courriel]);
    $utilisateur = $stmt->fetch();

    // Verifier si les informations reçu sont les bonnes.
    // Envoie de JSON pour la verification bonne ou mouvaise.
    if($utilisateur){
        if($utilisateur && password_verify($motDePasse, $utilisateur['mot_de_passe'])) {
            
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
                            "nom" => $utilisateur['nom']
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
                                "id" => $utilisateur['id'],
                                "courriel" => $utilisateur['courriel'],
                                "nom" => $utilisateur['nom']
                              ],
                              "jwt" => $jwt
                            ]);
        } else {
            echo json_encode(["token" => false , "message" => "Mot de passe incorrect"]); 
        }
    } else {
        echo json_encode(["token" => false, "message" => "Utilisateur incorrect"]);
    }

?>