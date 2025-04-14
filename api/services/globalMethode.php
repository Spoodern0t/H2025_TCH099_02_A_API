<?php
    require_once(__DIR__ . '/../../config/database.php');
    use \Firebase\JWT\JWT;
    use Firebase\JWT\Key;

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

        function transformCalendrierInfo(&$info_calendrier) {
            foreach ($info_calendrier as &$row) {
                if (isset($row['id_utilisateur'])) {
                    $row['id_utilisateur'] = (int)$row['id_utilisateur'];
                }
        
                if (isset($row['id_calendrier'])) {
                    $row['id_calendrier'] = (int)$row['id_calendrier'];
                }
        
                if (isset($row['id_evenement'])) {
                    $row['id_evenement'] = (int)$row['id_evenement'];
                }
        
                if (isset($row['id_element'])) {
                    $row['id_element'] = (int)$row['id_element'];
                }
        
                if (isset($row['est_membre'])) {
                    $row['est_membre'] = ($row['est_membre'] == 1) ? true : false;
                }
            }
        }
        function verfierExpirationToken($token) {
            header('Content-Type: application/json');

            try{

                $decode = JWT::decode($token, new Key(constant('CLE_SECRETE'), 'HS256'));
                
                return
                [
                    "status" => true,
                    "message" => "Token valide",
                    "utilisateur" => $decode->id_utilisateur ?? null,
                    "exp" => $decode->exp ?? null
                ];

            } catch(\Firebase\JWT\ExpiredException $e) {
                return ["status" => false, "message" => "token expiré"];
            } catch(Exception $e){
                return ["status" => false, "message" => "token invalide"];
            }

        }
    }

?>