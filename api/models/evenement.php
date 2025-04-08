<?php
    require 'vendor/autoload.php';
    require './api/services/globalMethode.php';
    require './config/config.php';
    use \Firebase\JWT\JWT;

    class Evenement {
        public $global;

        public function __construct() {
            $this->global = new GlobalMethode();
        }

    

    //TODO Requetes modifier parler avec mes coequipiers
    // A completer
    function creerEvenement($id_calendrier){
        header('Content-type: application/json');

        $data = json_decode(file_get_contents("php://input"), true);

        $pdo = $this->global->getPdo();

        $token = $data['token'];
        $titre = $data['titre'];
        $description = $data['description'];
        $couleur = $data['couleur'];

        $id_utilisateur = $this->global->verifierToken($token);

        echo $id_utilisateur;
    }
}

?>