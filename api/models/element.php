<?php
    require 'vendor/autoload.php';
    require './api/services/globalMethode.php';
    require './config/config.php';

    class Element{
        public $global;

        public function __construct() {
            $this->global = new GlobalMethode();
        }

        // TODO reparler de la requetes
        function creerElement($id_calendrier){
            header('Content-type: application/json');
            $data = json_decode(file_get_contents("php://input"), true);
            $pdo = $this->global->getPdo();

            $token = $data['token'];
            $nom = $data['nom'];
            $description = $data['description'];
            $evenement = $data['evenement'];
            $id_evenement = $evenement['id'];

            $id_utilisateur = $this->global->verifierToken($token);
            if(!$id_utilisateur){
                echo json_encode(["token" => false]);
                return;
            }

            try{
                $pdo->beginTransaction();



                $pdo->commit();
            }catch(\Throwable $e){
                $pdo->rollback();
                echo json_encode(["token" => false]);
            }


            echo $id_evenement;

        }
    }



?>