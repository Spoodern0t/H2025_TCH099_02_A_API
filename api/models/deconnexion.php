<?php
    require 'vendor/autoload.php';
    require './api/services/globalMethode.php';
    require './config/config.php';

    class Deconnexion{
        public $global;

        public function __construct() {
            $this->global = new GlobalMethode();
        }
    
        function deconnexion(){
            header('Content-Type: application/json');
            $data = json_decode(file_get_contents("php://input"), true);
            $pdo = $this->global->getPdo();

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

            $token = $data['token'];

            $stmt = $pdo-> prepare("DELETE FROM Connexion WHERE token = ?");
            $stmt-> execute([$token]);
            
            if($stmt->rowCount() > 0){
                http_response_code(200);
            } else {
                echo json_encode(["token" => false]);
            }
        }
    }

?>