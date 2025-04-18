<?php
    require 'vendor/autoload.php';
    require './api/services/globalMethode.php';
    require './config/config.php';
    use \Firebase\JWT\JWT;
    use Firebase\JWT\Key;


    class Token{
        private $key;

        public function __construct() {
            $this->key = constant('CLE_SECRETE');
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
            //$data = json_decode(file_get_contents("php://input"), true);

            echo $tokenEmail;
        }
    }

?>