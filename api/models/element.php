<?php
    require 'vendor/autoload.php';
    require './api/services/globalMethode.php';
    require './config/config.php';

    class Element{
        public $global;

        public function __construct() {
            $this->global = new GlobalMethode();
        }


        function creerElement($id_calendrier){
            header('Content-type: application/json');
            $data = json_decode(file_get_contents("php://input"), true);
            $pdo = $this->global->getPdo();

            echo $id_calendrier;

            
        }
    }



?>