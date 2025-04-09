<?php
    require 'vendor/autoload.php';
    require './api/services/globalMethode.php';
    require './config/config.php';
    use \Firebase\JWT\JWT;

    class Connexion{
        public $global;

        public function __construct() {
            $this->global = new GlobalMethode();
        }
    
        function connexion(){
            header('Content-Type: application/json');
            $data = json_decode(file_get_contents("php://input"), true);
            $pdo = $this->global->getPdo();

            if (!isset($pdo)) {
                die(json_encode(["error" => "Erreur de connexion à la base de données"]));
            }

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

                    $stmt = $pdo->prepare("SELECT id_utilisateur, id_calendrier, nom_utilisateur, est_membre
                        FROM Vue_utilisateur_Calendrier WHERE id_utilisateur = ?");
                    $stmt->execute([$utilisateur['id_utilisateur']]);
                    $info_calendrier = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    $this->global->creerVue();

                    try {
                        $pdo->beginTransaction();

                        $stmt = $pdo->prepare("INSERT INTO Connexion (token, id_utilisateur) VALUES (?, ?)");
                        $stmt->execute([$token, $utilisateur['id_utilisateur']]);

                        $pdo->commit();

                        echo json_encode
                        ([
                            "email" => $courriel,
                            "username" => $utilisateur["nom"],
                            "token" => $token,
                            "userCalendars" => $info_calendrier
                        ]);

                    } catch (\Throwable $e){
                        $pdo->rollBack();
                        echo $e->getMessage();
                        echo json_encode(["token" => false]);
                    }

                } else {
                    echo json_encode(["token" => false]); 
                }
            } else {
                echo json_encode(["token" => false]);
            }
        }

        
    }


?>