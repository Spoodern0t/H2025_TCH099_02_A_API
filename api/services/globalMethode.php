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
        function creerVue(){
            header('Content-type: application/json');

            $this->pdo->exec("DROP VIEW IF EXISTS Vue_Utilisateur_Calendrier");
            $stmt = $this->pdo->prepare(
                "CREATE VIEW Vue_utilisateur_Calendrier AS SELECT
                    Utilisateur.id_utilisateur,
                    Calendrier.id_calendrier,
                    Utilisateur.nom AS nom_utilisateur,
                    Utilisateur_Calendrier.est_membre,
                    Calendrier.nom AS nom_calendrier,
                    Calendrier.description
                FROM
                    Utilisateur
                INNER JOIN 
                    Calendrier ON Utilisateur.id_utilisateur = Calendrier.auteur_id
                INNER JOIN
                    Utilisateur_Calendrier ON Calendrier.id_calendrier = Utilisateur_Calendrier.id_calendrier"
            );
            $stmt->execute();
        }

    }

?>