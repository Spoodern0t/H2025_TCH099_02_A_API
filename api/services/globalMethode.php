<?php
    require_once(__DIR__ . '/../../config/database.php');

    class GlobalMethode {
        private $pdo;
        
        public function __construct() {
            $this->pdo = Database::getConnexion();
        }
        
        public function getPdo(){
            return $this->pdo;
        }

        function verifierToken($token){    
            
            $stmt = $this->pdo->prepare("SELECT id_utilisateur FROM Connexion WHERE token = ?");
            $stmt->execute([$token]);
            $id_utilisateur = $stmt->fetchColumn();
     
            if($id_utilisateur !== false){
                return $id_utilisateur;
            } else {
                return false;
            }
        }

    }

?>