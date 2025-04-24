<?php
    require 'vendor/autoload.php';
    require './api/services/globalMethode.php';
    require './config/config.php';
    use \Firebase\JWT\JWT;
    use Firebase\JWT\Key;

    class Token{
        private $key;
        public $global;

        public function __construct() {
            $this->key = constant('CLE_SECRETE');
            $this->global = new GlobalMethode();
        }
    
        function verifierExpiration($token) {
            header('Content-Type: application/json');

            try{

                $decode = JWT::decode($token, new Key($this->key, 'HS256'));
                
                echo json_encode
                ([
                    "message" => "Token valide",
                    "utilisateur" => $decode->id_utilisateur ?? null,
                    "exp" => $decode->exp ?? null
                ]);

            } catch(\Firebase\JWT\ExpiredException $e) {
                http_response_code(401);
                echo json_encode(["message" => "token expiré"]);
            } catch(Exception $e){
                http_response_code(401);
                echo json_encode(["message" => "token invalide"]);
            }
        }

        function verifierTokenEmail($tokenEmail){
            header('Content-Type: application/json');
            $data = json_decode(file_get_contents("php://input"), true);
            $pdo = $this->global->getPdo();

            try{
                $decoded = JWT::decode($tokenEmail, new Key($this->key, 'HS256'));

                $email = $decoded->email ?? null;
                $motDePasse = $decoded->motDePasse ?? null;

                if($email === null || $motDePasse === null){
                    echo json_encode(["message" => "Email non valide dans le token"]);
                    exit();
                }

                try{
                    $pdo->beginTransaction();

                    $stmt = $pdo->prepare("UPDATE Utilisateur SET est_valide = ? WHERE courriel=?");
                    $stmt->execute([true,$email]);

                    $pdo->commit();
                }catch(\Throwable $e){
                    $pdo->rollBack();
                    http_response_code(401);
                    echo json_encode(["message" => "La validation du client à échouer."]);
                }

            } catch(\Firebase\JWT\ExpiredException $e){
                http_response_code(400);
                echo json_encode(["message" => "Le token à expirer."]);
            } catch(Exception $e){
                http_response_code(400);
                echo json_encode(["message" => "Une erreur est survenue lors de la vérification du token."]);
            }
        }
    }

?>